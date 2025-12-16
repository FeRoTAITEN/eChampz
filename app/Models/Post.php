<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'content',
        'type',
        'views',
        'upvotes',
        'downvotes',
        'shares',
    ];

    protected $casts = [
        'views' => 'integer',
        'upvotes' => 'integer',
        'downvotes' => 'integer',
        'shares' => 'integer',
    ];

    /**
     * Get the user that owns the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the mentions for the post.
     */
    public function mentions(): HasMany
    {
        return $this->hasMany(PostMention::class)->orderBy('position');
    }

    /**
     * Get the users mentioned in the post.
     */
    public function mentionedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_mentions')
            ->withPivot('position', 'length')
            ->withTimestamps();
    }

    /**
     * Get the media for the post.
     */
    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class)->orderBy('order');
    }

    /**
     * Get the games tagged in the post.
     */
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'post_game');
    }

    /**
     * Get content as pre-processed segments for frontend rendering.
     */
    public function getContentSegmentsAttribute(): array
    {
        $content = $this->content;
        $mentions = $this->mentions()->with('user')->get()->sortBy('position');
        
        if ($mentions->isEmpty()) {
            return [
                ['type' => 'text', 'value' => $content]
            ];
        }

        $segments = [];
        $lastPos = 0;
        
        foreach ($mentions as $mention) {
            // Add text before mention
            if ($mention->position > $lastPos) {
                $textBefore = substr($content, $lastPos, $mention->position - $lastPos);
                if ($textBefore !== '') {
                    $segments[] = [
                        'type' => 'text',
                        'value' => $textBefore
                    ];
                }
            }
            
            // Add mention segment
            $segments[] = [
                'type' => 'mention',
                'username' => $mention->user->username,
                'user_id' => $mention->user_id,
                'name' => $mention->user->name,
                'display' => '@' . $mention->user->username,
            ];
            
            $lastPos = $mention->position + $mention->length;
        }
        
        // Add remaining text after last mention
        if ($lastPos < strlen($content)) {
            $remainingText = substr($content, $lastPos);
            if ($remainingText !== '') {
                $segments[] = [
                    'type' => 'text',
                    'value' => $remainingText
                ];
            }
        }
        
        return $segments;
    }
}


