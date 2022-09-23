<?php

namespace App\GetVideo\Extractors;

use App\GetVideo\BaseExtractor;

class Dailymotion extends BaseExtractor
{
    const _valid_url = "/https?:\/\/(?:(www|touch)\.)?dailymotion\.[a-z]{2,3}\/(?:(?:(?:embed|swf|#)\/)?video|swf)\/(?P<id>[^\/?_]+)/i";
    const _c2mp3 = true;
    const _formats = [
        'http-144' => '144p',
        'http-240' => '240p',
        'http-380' => '380p',
        'http-480' => '480p',
        'http-720' => '720p',
        'http-1080' => '1080p',
        'http-1440' => '1440p',
        'http-2160' => '2160p'
    ];

    public static function is_valid(string $url): bool
    {
        return preg_match(self::_valid_url, $url);
    }

    public function __construct(string $url)
    {
        parent::__construct($url);

        $this->_valid_url = self::_valid_url;
        $this->_formats = self::_formats;
    }
}
