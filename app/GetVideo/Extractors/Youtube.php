<?php

namespace App\GetVideo\Extractors;

use DateTime;
use DateInterval;
use DateTimeInterface;
use App\GetVideo\Utils;
use App\GetVideo\BaseExtractor;

class Youtube extends BaseExtractor
{
    const _valid_url = "~^((?:https?://|//)(?:(?:(?:(?:\w+\.)?[yY][oO][uU][tT][uU][bB][eE](?:-nocookie|kids)?\.com|(?:www\.)?deturl\.com/www\.youtube\.com|(?:www\.)?pwnyoutube\.com|(?:www\.)?hooktube\.com|(?:www\.)?yourepeat\.com|tube\.majestyc\.net|youtube\.googleapis\.com)/(?:.*?\#/)?(?:(?:(?:v|embed|e|shorts)/(?!videoseries|live_stream))|(?:(?:(?:watch|movie)(?:_popup)?(?:\.php)?/?)?(?:\?|\#!?)(?:.*?[&;])??v=)))|(?:youtu\.be|vid\.plus|zwearz\.com/watch)/|(?:www\.)?cleanvideosearch\.com/media/action/yt/watch\?videoId=))?(?P<id>[0-9A-Za-z_-]{11})(?(1).+)?(?:\#|$)~i";
    const _c2mp3 = true;
    const _formats = [
        17 => 144,
        18 => 360,
        22 => 720,
        36 => 240,
        43 => 360
    ];

    private string $_url;

    public static function is_valid(string $url): bool
    {
        return preg_match(self::_valid_url, $url);
    }

    public function __construct(string $url)
    {
        $this->url = $url;
        $this->_valid_url = self::_valid_url;
        $this->_formats = self::_formats;

        $video_id = $this->_match_id($this->url);
        $this->_url = 'http://youtube.com/watch?v=' . $video_id;
    }

    public function get_url(): string
    {
        return $this->_url;
    }

    public function get_identifier(): string
    {
        return sha1($this->_url);
    }

    protected function _extract(): array
    {
        return $this->_get_ytdl($this->_url);
    }

    public static function process_formats($info)
    {
        $formats = [];

        $expiresAt = (new DateTime())->add(new DateInterval('PT6H'));
        $expiresAtRFC3339_EXTENDED = $expiresAt->format(DateTimeInterface::RFC3339_EXTENDED);

        foreach (array_reverse($info['formats']) as $format) {
            $acodec = Utils::str_to_bool($format['acodec']);
            $vcodec = Utils::str_to_bool($format['vcodec']);

            if (!$acodec && !$vcodec) continue;

            $fmt = [
                'type' => 'video',
                'url' => $format['url'],
                'ext' => $format['ext'],
                // 'http_headers' => $format['http_headers'],
                'expires_at' => $expiresAtRFC3339_EXTENDED,
            ];

            if (isset($format['filesize'])) {
                $fmt['filesize'] = $format['filesize'];
            }

            if (isset($format['dynamic_range'])) {
                $fmt['dynamic_range'] = $format['dynamic_range'];
            }

            if (isset($format['language']) && !empty($format['language'])) {
                $fmt['language'] = $format['language'];

                if (isset($format['language_preference'])) {
                    $fmt['language_preference'] = $format['language_preference'];
                }
            }

            // only video
            if (!$acodec) {
                $fmt['resolution'] = intval(preg_replace('/p([0-9]{2})?/i', '', $format['format_note']));
                $fmt['label'] = preg_replace('/p([0-9]{2})/i', 'p', $format['format_note']) . ' - ' . strtoupper($format['ext']);
                $fmt['fps'] = $format['fps'];
                $fmt['acodec'] = false;
                $fmt['vcodec'] = $format['vcodec'];
            }

            // only audio
            if (!$vcodec) {
                $fmt['type'] = 'audio';
                $fmt['bitrate'] = $format['abr'];
                $fmt['label'] = strtoupper($format['ext']) . ' - (' . $format['abr'] . ' kbps)';
                $fmt['acodec'] = $format['acodec'];
                $fmt['vcodec'] = false;
            }

            // video and audio
            if ($acodec && $vcodec) {
                $fmt['resolution'] = self::_formats[$format['format_id']];
                $fmt['label'] = self::_formats[$format['format_id']] . 'p' . ' - ' . strtoupper($format['ext']);
                $fmt['acodec'] = $format['acodec'];
                $fmt['vcodec'] = $format['vcodec'];
            }

            array_push($formats, $fmt);
        }

        return [
            'title' => $info['title'],
            'webpage_url' => $info['webpage_url'],
            'thumbnail_url' => $info['thumbnail'],
            'formats' => $formats
        ];
    }
}
