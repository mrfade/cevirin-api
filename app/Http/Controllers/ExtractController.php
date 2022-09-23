<?php

namespace App\Http\Controllers;

use App\GetVideo\ExtractionFailedException;
use App\Http\Controllers\Controller;
use App\GetVideo\ExtractorFactory;
use App\Models\ApiRequest;
use App\Models\DownloadToken;
use App\Models\Video;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use TypeError;

class ExtractController extends Controller
{

    public function extract(Request $request)
    {
        $start = microtime(true);

        $ip = get_ip();
        $total_quota = 1000;
        $todaysDate = (new DateTime())->format('Y-m-d');

        $validated = $request->validate([
            'url' => 'required|url',
        ]);

        $url = $validated['url'];

        // Extractor
        $extractor = ExtractorFactory::createFromUrl($url);

        // Check in cache
        $identifier = 'extract:' . $extractor->get_identifier();
        if (Redis::exists($identifier)) {
            $cache = Redis::get($identifier);
            $cache = json_decode($cache, true);
        } else {

            try {
                $extracted = $extractor->real_extract();
            } catch (ExtractionFailedException | TypeError | Exception $e) {
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
                    'token' => Video::createToken(),
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
        $identifier = 'video:' . $video->token;
        if (Redis::exists($identifier)) {
            $cache = Redis::get($identifier);
            $result = json_decode($cache, true);
        } else {
            $result = [
                'token' => $video->token,
                'title' => $video->title,
                'thumbnail' => url('thumbnail', [$video->token]),
                'thumbnail_no_proxy' => $video->thumbnail,
                'sources' => []
            ];

            // Insert the download tokens
            foreach ($cache['formats'] as $format) {
                $downloadToken = DownloadToken::create([
                    'video_id' => $video->id,
                    'token' => DownloadToken::createToken(),
                    'url' => $format['url'],
                    'ext' => $format['ext'],
                    'headers' => json_encode($format['http_headers']),
                    'ip' => $ip,
                    'expires_at' => (new DateTime())->add(new DateInterval('PT8H'))->format('Y-m-d H:i:s'),
                ]);

                unset($format['url']);
                unset($format['http_headers']);

                array_push($result['sources'], [
                    'token' => $downloadToken->token,
                    'url' => 'https://dl-ams1-v2.cevir.in/' . $downloadToken->token . '.' . $downloadToken->ext,
                    // 'url_no_proxy' => $downloadToken->url,
                    ...$format
                ]);
            }

            ApiRequest::create([
                'video_id' => $video->id,
                'ip' => $ip
            ]);

            Redis::set($identifier, json_encode($result), 'EX', 600);
        }

        // Calculate quota left
        $quota_used = ApiRequest::where('ip', $ip)
            ->whereDate('created_at', $todaysDate)
            ->count();
        $quota = [
            'quota' => $total_quota,
            'quota_used' => $quota_used,
            'quota_left' => $total_quota - $quota_used
        ];

        $extraction_time = round((microtime(true) - $start) * 1000, 1);
        $execution_time = round((microtime(true) - LARAVEL_START) * 1000, 1);

        return response()->json([
            ...$result,
            ...$quota,
            'extraction_time' => $extraction_time . 'ms',
            'execution_time' => $execution_time . 'ms'
        ]);
    }
}
