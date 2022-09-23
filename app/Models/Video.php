<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Video extends Model
{
    protected $fillable = [
        'token',
        'url',
        'title',
        'thumbnail'
    ];

    public static function createToken()
    {
        do {
            $token = Uuid::uuid4()->toString();
        } while (Video::where('token', $token)->exists());

        return $token;
    }
}
