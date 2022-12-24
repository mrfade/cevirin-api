<?php

namespace App\Http\Controllers;

use DateTime;
use Throwable;
use DateInterval;
use App\Models\Video;
use DateTimeInterface;
use App\Models\ApiRequest;
use Illuminate\Http\Request;
use App\Models\DownloadToken;
use App\GetVideo\ExtractorFactory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use App\GetVideo\Exceptions\ExtractionFailedException;
use App\GetVideo\Exceptions\URLNotSupportedException;

class ExtractController extends Controller
{

    public function extract(Request $request)
    {
        $start = microtime(true);

        $ip = get_ip();
        $totalQuota = 1000;
        $todaysDate = (new DateTime())->format('Y-m-d');

        $validated = $request->validate([
            'url' => 'required|url',
        ]);

        $url = $validated['url'];

        try {
            // Extractor
            $extractor = ExtractorFactory::createFromUrl($url);
        } catch (URLNotSupportedException | Throwable $e) {
            // URL not supported error
            return response()->json([
                'status' => 'error',
                'code' => 2002,
                'message' => 'See: ' . url('api/error-codes')
            ], 500);
        }

        // Check in cache
        $identifier = 'extract:' . $extractor->get_identifier();
        if (Redis::exists($identifier)) {
            $cache = Redis::get($identifier);
            $cache = json_decode($cache, true);
        } else {

            try {
                $extracted = $extractor->real_extract();
            } catch (ExtractionFailedException | Throwable $e) {
                report($e);

                return response()->json([
                    'status' => 'error',
                    'code' => isset($e->getErrorCode) ? $e->getErrorCode() : 2001,
                    'message' => isset($e->getErrorMessage) ? $e->getErrorMessage() : 'See: ' . url('api/error-codes')
                ], 500);
            }

            $cache = $extracted;
            Redis::set($identifier, json_encode($extracted), 'EX', 600);

            // Create the video if not exists
            if (!Video::where('url', $cache['webpage_url'])->exists()) {
                Video::create([
                    'url' => $cache['webpage_url'],
                    'title' => $cache['title'],
                    'thumbnail' => $cache['thumbnail_url']
                ]);
            }
        }

        // Get the video
        $video = Video::firstWhere('url', $cache['webpage_url']);

        // Get from cache if exists otherwise cache the result
        $result = [];
        $identifier = 'video:' . $video->id;
        if (Redis::exists($identifier)) {
            $cache = Redis::get($identifier);
            $result = json_decode($cache, true);
        } else {
            $result = [
                'token' => $video->id,
                'title' => $video->title,
                'thumbnail' => url('thumbnail', [$video->id]),
                'thumbnail_no_proxy' => $video->thumbnail,
                'sources' => []
            ];

            $eightHoursLater = (new DateTime())->add(new DateInterval('PT8H'));
            $eightHoursLaterYmdHis = $eightHoursLater->format('Y-m-d H:i:s');
            $eightHoursLaterRFC3339_EXTENDED = $eightHoursLater->format(DateTimeInterface::RFC3339_EXTENDED);

            // Insert the download tokens
            foreach ($cache['formats'] as $format) {
                $downloadToken = DownloadToken::create([
                    'video_id' => $video->id,
                    'url' => $format['url'],
                    'ext' => $format['ext'],
                    'headers' => isset($format['http_headers']) ? json_encode($format['http_headers']) : null,
                    'ip' => $ip,
                    'expires_at' => $format['expires_at_ymdhis'] ?? $eightHoursLaterYmdHis,
                ]);

                unset($format['url']);
                unset($format['http_headers']);
                unset($format['expires_at_ymdhis']);

                array_push($result['sources'], [
                    'token' => $downloadToken->id,
                    'url' => route('file.redirect', ['downloadToken' => $downloadToken]),
                    // 'url_no_proxy' => $downloadToken->url,
                    ...$format,
                    'expires_at' => $format['expires_at'] ?? $eightHoursLaterRFC3339_EXTENDED,
                ]);
            }

            ApiRequest::create([
                'video_id' => $video->id,
                'ip' => $ip
            ]);

            Redis::set($identifier, json_encode($result), 'EX', 600);
        }

        // Calculate quota left
        $quotaUsed = ApiRequest::where('ip', $ip)
            ->whereDate('created_at', $todaysDate)
            ->count();
        $quota = [
            'quota' => $totalQuota,
            'quota_used' => $quotaUsed,
            'quota_left' => $totalQuota - $quotaUsed
        ];

        $nowMicrotime = microtime(true);
        $extractionTime = round(($nowMicrotime - $start) * 1000, 1);
        $executionTime = round(($nowMicrotime - LARAVEL_START) * 1000, 1);

        return response()->json([
            ...$result,
            ...$quota,
            'extraction_time' => $extractionTime . 'ms',
            'execution_time' => $executionTime . 'ms'
        ]);
    }
}
