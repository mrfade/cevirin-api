<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiRequest extends Model
{
    protected $fillable = [
        'video_id',
        'ip',
        'api_key_id',
    ];
}
