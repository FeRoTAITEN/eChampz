<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Game extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon_url',
    ];

    /**
     * Get the posts that have this game tag.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_game');
    }

    /**
     * Get the users who favorited this game.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'game_user')->withTimestamps();
    }
}


