<?php

namespace App\GetVideo\Extractors;

use App\GetVideo\BaseExtractor;

class PuhuTV extends BaseExtractor
{
    const _valid_url = "/https?:\/\/(?:www\.)?puhutv\.com\/(?P<id>[a-z0-9-]+)\-izle/i";
    const _c2mp3 = false;
    const _formats = [
        'http-144p' => '144p',
        'http-240p' => '240p',
        'http-360p' => '360p',
        'http-480p' => '480p',
        'http-720p' => '720p',
        'http-1080p' => '1080p',
        'http-1440p' => '1440p',
        'http-2160p' => '2160p'
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
