<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DownloadToken extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'video_id',
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
