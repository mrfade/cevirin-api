<?php

namespace App\GetVideo\Extractors;

use App\GetVideo\BaseExtractor;

class Odnoklassniki extends BaseExtractor
{
    const _valid_url = "/https?:\/\/(?:(?:www|m|mobile)\.)?(?:odnoklassniki|ok)\.ru\/(?:video(?:embed)?\/|web-api\/video\/moviePlayer\/|live\/|dk\?.*?st\.mvId=)(?P<id>[\d-]+)/i";
    const _c2mp3 = true;
    const _formats = [
        'mobile' => '144p',
        'lowest' => '240p',
        'low' => '360p',
        'sd' => '480p',
        'hd' => '720p',
        'full' => '1080p'
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
        $this->_url = 'http://ok.ru/video/' . $video_id;
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
}
