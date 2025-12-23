<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGame extends Model
{
    protected $fillable = [
        'user_id',
        'platform_account_id',
        'platform_game_id', // FK to platform_games
        'total_playtime_minutes',
        'trophies_bronze',
        'trophies_silver',
        'trophies_gold',
        'trophies_platinum',
        'trophies_total',
        'trophies_earned',
        'trophy_progress_percentage',
        'platform_specific_data', // JSON for platform-specific data (trophies, achievements, etc.)
        'metadata',
        'last_played_at',
        'last_synced_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'platform_specific_data' => 'array',
        'last_played_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'total_playtime_minutes' => 'integer',
        'trophies_bronze' => 'integer',
        'trophies_silver' => 'integer',
        'trophies_gold' => 'integer',
        'trophies_platinum' => 'integer',
        'trophies_total' => 'integer',
        'trophies_earned' => 'integer',
        'trophy_progress_percentage' => 'integer',
    ];

    /**
     * Get the user that owns this game.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the platform account for this game.
     */
    public function platformAccount(): BelongsTo
    {
        return $this->belongsTo(PlatformAccount::class);
    }

    /**
     * Get the platform game (shared game data).
     */
    public function platformGame(): BelongsTo
    {
        return $this->belongsTo(PlatformGame::class);
    }

    /**
     * Get playtime in hours.
     */
    public function getPlaytimeHoursAttribute(): float
    {
        return round($this->total_playtime_minutes / 60, 2);
    }
}
