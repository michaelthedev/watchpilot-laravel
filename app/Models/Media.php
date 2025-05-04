<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Media extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'release_date' => 'date',
        'last_fetched_at' => 'datetime',
    ];

    protected $hidden = [
        'id',
        'pivot',
        'poster',
        'last_synced_at'
    ];

    protected $appends = [
        'poster_url',
    ];

    // Get full poster URL
    public function getPosterUrlAttribute(): ?string
    {
        if ($this->poster) {
            return 'https://image.tmdb.org/t/p/w500' . $this->poster;
        }

        return null;
    }

    public function watchlists(): BelongsToMany
    {
        return $this->belongsToMany(Watchlist::class, 'watchlist_items');
    }

    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'likes')
            ->withTimestamps();
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(UserReminders::class);
    }

    // function to update or create media record
    public static function upsertItem(array $data): Media
    {
        return self::updateOrCreate(
            [
                'tmdb_id' => $data['_id'],
                'type' => $data['type'],
            ],
            [
                'title' => $data['title'],
                'poster' => $data['poster'],
                'release_date' => $data['release_date'] ?? null,
                'last_synced_at' => now(),
            ]
        );
    }
}
