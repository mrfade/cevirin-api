<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DownloadToken extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $connection = 'mongodb';
    protected $dates = ['expires_at'];

    protected $fillable = [
        'url',
        'ext',
        'headers',
        'ip',
        'expires_at',
    ];

    /**
     * Generate a new UUID for the model.
     *
     * @return string
     */
    public function newUniqueId()
    {
        return (string) Uuid::uuid4();
    }
}
