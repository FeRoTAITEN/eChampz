<?php

namespace App\Models;

use App\Enums\UserRank;
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
        'avatar',
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
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'avatar_url',
        'rank',
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
     * Get the platform-linked games for the user (PlayStation, etc.).
     */
    public function platformGames(): HasMany
    {
        return $this->hasMany(UserGame::class);
    }

    /**
     * Get the user's favorite games (from catalog).
     */
    public function favoriteGames(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_user')->withTimestamps();
    }

    /**
     * Get the avatar URL attribute.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) {
            return null;
        }

        return asset('storage/' . $this->avatar);
    }

    /**
     * Get user's rank based on current XP (computed).
     */
    public function getRankAttribute(): ?string
    {
        $rankModel = Rank::getRankForXp($this->xp_total);
        return $rankModel ? $rankModel->name : 'bronze';
    }
}
