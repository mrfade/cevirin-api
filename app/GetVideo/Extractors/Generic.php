<?php

namespace App\GetVideo\Extractors;

use App\GetVideo\Utils;
use App\GetVideo\BaseExtractor;

class Generic extends BaseExtractor
{
    const _valid_url = '/https?:\/\/(?:www\.)?(?P<domain>[^\/]+\.[a-zA-Z]+)\/(?P<id>.+)/i';
    const _c2mp3 = false;

    public static function is_valid(string $url): bool
    {
        return preg_match(self::_valid_url, $url);
    }

    public function __construct(string $url)
    {
        parent::__construct($url);

        $this->_valid_url = self::_valid_url;
    }

    public static function process_formats($info)
    {
        $formats = [];

        if ($info['formats']) {

            foreach (array_reverse($info['formats']) as $format) {
                if ($format['protocol'] === 'm3u8_native') continue;

                $label = isset($format['height']) ? $format['height'] . 'p - ' : '';

                array_push($formats, [
                    'type' => 'video',
                    'resolution' => isset($format['height']) ? $format['height'] : null,
                    'label' => $label . strtoupper($format['ext']),
                    'name' => $info['title'] . '.' . $format['ext'],
                    'ext' => $format['ext'],
                    'fps' => isset($format['fps']) ? $format['fps'] : null,
                    'url' => $format['url'],
                    'http_headers' => $format['http_headers']
                ]);
            }
        } else {

            $detect_ext = Utils::determine_extension($info['title']);
            $ext = $detect_ext === $info['ext'] ? $info['title'] : $info['title'] . '.' . $info['ext'];

            $label = isset($info['height']) ? $info['height'] . 'p - ' : '';

            array_push($formats, [
                'type' => 'video',
                'resolution' => isset($info['height']) ? $info['height'] : null,
                'label' => $label . strtoupper($info['ext']),
                'name' => $ext,
                'ext' => $info['ext'],
                'url' => $info['url'],
                'http_headers' => $info['http_headers']
            ]);
        }

        return [
            'title' => $info['title'],
            'webpage_url' => $info['webpage_url'],
            'thumbnail_url' => $info['thumbnail'],
            'formats' => $formats
        ];
    }
}
