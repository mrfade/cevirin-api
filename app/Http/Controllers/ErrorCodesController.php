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

            2001 => 'unknown error',
            2002 => 'url not supported',
            2003 => 'file not found',
            2004 => 'extraction failed',
            2005 => 'rate limit exceeded',
            2101 => 'no support for adult content',

            1001 => 'invalid request'
        ];

        return response()->json([
            'status' => 'success',
            'codes' => $codes
        ]);
    }
}
