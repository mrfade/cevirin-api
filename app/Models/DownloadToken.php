<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class DownloadToken extends Model
{
    protected $fillable = [
        'video_id',
        'token',
        'url',
        'ext',
        'headers',
        'ip',
        'expires_at',
    ];

    public static function createToken()
    {
        do {
            $token = Uuid::uuid4()->toString();
        } while (DownloadToken::where('token', $token)->exists());

        return $token;
    }
}
