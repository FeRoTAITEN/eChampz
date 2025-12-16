<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMedia extends Model
{
    protected $fillable = [
        'post_id',
        'type',
        'url',
        'thumbnail_url',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get the post that owns the media.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}


