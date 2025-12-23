<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformGame extends Model
{
    protected $fillable = [
        'platform',
        'platform_game_id',
        'name',
        'icon_url',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get all user games for this platform game.
     */
    public function userGames(): HasMany
    {
        return $this->hasMany(UserGame::class);
    }

    /**
     * Scope for filtering by platform.
     */
    public function scopeForPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }
}
