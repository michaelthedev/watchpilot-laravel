<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Watchlist extends Model
{
    protected $guarded = ['id'];

    protected $hidden = [
        'id', 'user_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WatchlistItem::class);
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'watchlist_items');
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    public function isPublic(): bool
    {
        return $this->visibility == 'public';
    }

    #[Scope]
    protected function canBeViewed(Builder $query, ?User $user = null): void
    {
        $query->where(function($query) use($user) {
            $query->whereVisibility('public')
                ->orWhereBelongsTo($user);
        });
    }
}
