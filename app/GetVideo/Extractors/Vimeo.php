<?php

namespace App\GetVideo\Extractors;

use App\GetVideo\BaseExtractor;

// https://vimeo.com/203586645 ## HDR

class Vimeo extends BaseExtractor
{
    const _valid_url = "/https?:\/\/(?:(?:www|(?:player))\.)?vimeo(?:pro)?\.com\/(?!(?:channels|album)\/[^\/?#]+\/?(?:$|[?#])|[^\/]+\/review\/|ondemand\/)(?:.*?\/)?(?:(?:play_redirect_hls|moogaloop\.swf)\?clip_id=)?(?:videos?\/)?(?P<id>[0-9]+)(?:\/[\da-f]+)?\/?(?:[?&].*)?(?:[#].*)?$/ix";
    const _c2mp3 = true;
    const _formats = [
        'http-360p' => '360p',
        'http-540p' => '540p',
        'http-720p' => '720p',
        'http-1080p' => '1080p'
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
