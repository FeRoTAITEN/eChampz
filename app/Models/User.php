<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'date_of_birth',
        'represent_type',
        'organization_name',
        'position',
        'onboarding_completed_at',
        'xp_total',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Check if user is a gamer
     */
    public function isGamer(): bool
    {
        return $this->role === UserRole::GAMER;
    }

    /**
     * Check if user is a recruiter
     */
    public function isRecruiter(): bool
    {
        return $this->role === UserRole::RECRUITER;
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(UserRole|string $role): bool
    {
        if (is_string($role)) {
            $role = UserRole::from($role);
        }

        return $this->role === $role;
    }

    /**
     * Get the email verification codes for the user.
     */
    public function emailVerificationCodes(): HasMany
    {
        return $this->hasMany(EmailVerificationCode::class);
    }

    /**
     * Get the posts created by the user.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the posts where the user is mentioned.
     */
    public function mentionedInPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_mentions');
    }

    /**
     * Get the platform accounts for the user.
     */
    public function platformAccounts(): HasMany
    {
        return $this->hasMany(PlatformAccount::class);
    }

    /**
     * Get the PlayStation account for the user.
     */
    public function playstationAccount(): HasOne
    {
        return $this->hasOne(PlatformAccount::class)->where('platform', 'playstation');
    }

    /**
     * Get the games for the user.
     */
    public function games(): HasMany
    {
        return $this->hasMany(UserGame::class);
    }
}
