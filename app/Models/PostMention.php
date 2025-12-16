<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMention extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
        'position',
        'length',
    ];

    protected $casts = [
        'position' => 'integer',
        'length' => 'integer',
    ];

    /**
     * Get the post that owns the mention.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user that is mentioned.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


