<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ErrorCodesController extends Controller
{
    public function index(Request $request)
    {
        $codes = [
            5001 => "'option' value's file not found",
            5002 => 'auth required',

            3001 => 'get değerleri eksik',
            3002 => 'veritabanında bulunamıyor',
            3003 => 'izinsiz ip',
            3004 => 'dosya süresi dolmuş',

            'UNKNOWN_ERROR' => 'Unknown error.',
            'URL_NOT_SUPPORTED' => 'Url not supported.',
            'FILE_NOT_FOUND' => 'File not found.',
            'EXTRACTION_FAILED' => 'Extraction process failed.',
            'RATE_LIMIT_EXCEEDED' => 'Extraction rate limit exceeded. Default: 1000 extraction/day',
            'TOO_MANY_ATTEMPTS' => 'Too many requests. Default: 100 reqs/min',

            'NO_ADULT_CONTENT' => 'No support for adult content.',

            'INVALID_REQUEST' => 'Invalid request'
        ];

        return view('error-codes', [
            'codes' => $codes
        ]);
    }
}
