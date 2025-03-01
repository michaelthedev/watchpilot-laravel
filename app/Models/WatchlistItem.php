<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WatchlistItem extends Model
{
    protected $guarded = ['id'];

    public function watchlist(): BelongsTo
    {
        return $this->belongsTo(Watchlist::class);
    }
}
