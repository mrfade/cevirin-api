<?php

namespace App\GetVideo\Extractors;

use App\GetVideo\BaseExtractor;

class Facebook extends BaseExtractor
{
    const _valid_url = "/(?:https?:\/\/(?:[\w-]+\.)?(?:facebook\.com)\/(?:[^#]*?\#!\/)?(?:(?:video\/video\.php|photo\.php|video\.php|video\/embed|story\.php)\?(?:.*?)(?:v|video_id|story_fbid)=|[^\/]+\/videos\/(?:[^\/]+\/)?|[^\/]+\/posts\/|groups\/[^\/]+\/permalink\/)|facebook:)(?P<id>[0-9]+)/ix";
    const _c2mp3 = false;
    const _formats = [
        'dash_sd_src' => 'SD',
        'dash_hd_src' => 'HD'
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
