<?php

namespace App\GetVideo;

use App\GetVideo\Extractors\Vimeo;
use App\GetVideo\Extractors\PuhuTV;
use App\GetVideo\Extractors\Generic;
use App\GetVideo\Extractors\Youtube;
use App\GetVideo\Extractors\Facebook;
use App\GetVideo\Extractors\Dailymotion;
use App\GetVideo\Extractors\Odnoklassniki;
use App\GetVideo\Exceptions\URLNotSupportedException;

class ExtractorFactory
{

    public const classes = [
        'Youtube',
        'Dailymotion',
        'Facebook',
        'Vimeo',
        'Odnoklassniki',
        'PuhuTV'
    ];

    /**
     * Get Extractor class with namespace
     *
     * @param string $extractor
     * @return string
     */
    public static function getExtractorClass(string $extractor): string
    {
        return sprintf('\App\GetVideo\Extractors\%s', ucfirst($extractor));
    }

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
        $class = self::getExtractorClass($extractor);

        return new $class($url);
    }

    /**
     * Creates Extractor instance from url
     *
     * @param string $url
     * @return BaseExtractor
     */
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

        throw new URLNotSupportedException('URL does not match');
    }
}
