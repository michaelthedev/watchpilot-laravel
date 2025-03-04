<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class MediaLike extends Model
{
    protected $fillable = [
        'user_id',
        'media_id'
    ];

    protected $hidden = [
        'user_id',
    ];
}
