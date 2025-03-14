<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

final class User extends Authenticatable
    implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Relationships */
    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(MediaLike::class);
    public function likedMedia(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'user_likes')
            ->withTimestamps();
    }

    public function library(): HasMany
    {
        return $this->hasMany(UserLibrary::class);
    }

    // Check if user has liked a specific media
    public function hasLiked(Media $media): bool
    {
        return $this->likedMedia()->where('media_id', $media->id)->exists();
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
