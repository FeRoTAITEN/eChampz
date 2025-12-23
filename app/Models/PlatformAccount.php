<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class PlatformAccount extends Model
{
    protected $fillable = [
        'user_id',
        'platform',
        'platform_username',
        'platform_account_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'npsso_token',
        'is_verified',
        'last_synced_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
        'npsso_token',
    ];

    /**
     * Get the user that owns this platform account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the games for this platform account.
     */
    public function games(): HasMany
    {
        return $this->hasMany(UserGame::class);
    }

    /**
     * Encrypt access token before saving.
     */
    public function setAccessTokenAttribute($value): void
    {
        $this->attributes['access_token'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Decrypt access token when retrieving.
     */
    public function getAccessTokenAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Encrypt refresh token before saving.
     */
    public function setRefreshTokenAttribute($value): void
    {
        $this->attributes['refresh_token'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Decrypt refresh token when retrieving.
     */
    public function getRefreshTokenAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if token is expired.
     */
    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return true;
        }

        return $this->token_expires_at->isPast();
    }
}
