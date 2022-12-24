<?php

namespace App\Http\Controllers;

use App\Models\DownloadToken;
use Illuminate\Http\Request;

class FileRedirectController extends Controller
{
    public function redirect(Request $request, DownloadToken $downloadToken)
    {
        return redirect($downloadToken->url);
    }
}
