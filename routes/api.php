<?php

use Illuminate\Http\Request;
use App\Http\Middleware\ApiQuota;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExtractController;
use App\Http\Controllers\FileRedirectController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/extract', [ExtractController::class, 'extract'])->middleware(ApiQuota::class);
Route::get('/video/{video}/thumbnail', [VideoController::class, 'thumbnail'])->name('video.thumbnail');
Route::get('/r/{downloadToken}', [FileRedirectController::class, 'redirect'])->name('file.redirect');

Route::any('/', function () {
    return redirect('/docs');
});
