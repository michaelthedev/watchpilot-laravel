<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class SystemList extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'items' => 'array',
    ];
}
