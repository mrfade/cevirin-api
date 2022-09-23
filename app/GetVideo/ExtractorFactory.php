<?php

namespace App\GetVideo;

use App\GetVideo\Extractors\Dailymotion;
use App\GetVideo\Extractors\Facebook;
use App\GetVideo\Extractors\Generic;
use App\GetVideo\Extractors\Odnoklassniki;
use App\GetVideo\Extractors\PuhuTV;
use App\GetVideo\Extractors\Vimeo;
use App\GetVideo\Extractors\Youtube;
use Exception;

class ExtractorFactory
{

    /**
     * Create new extractor.
     *
     * @param string $extractor
     * @param string $url
     *
     * @return BaseExtractor
     */
    public static function create(string $extractor, string $url): BaseExtractor
    {
        $class = sprintf('\App\GetVideo\Extractors\%s', ucfirst($extractor));

        return new $class($url);
    }

    public static function createFromUrl(string $url): BaseExtractor
    {
        $_classes = [
            Youtube::class,
            Dailymotion::class,
            Facebook::class,
            Vimeo::class,
            Odnoklassniki::class,
            PuhuTV::class,
            Generic::class
        ];

        foreach ($_classes as $_class) {
            if ($_class::is_valid($url)) {
                return new $_class($url);
            }
        }

        throw new Exception('URL does not match');
    }
}
