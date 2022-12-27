<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function thumbnail(Request $request, Video $video)
    {
        redirect($video->thumbnail);
    }
}
