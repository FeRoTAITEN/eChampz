<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'question',
        'answer',
        'order',
        'is_active',
        'views',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_active' => 'boolean',
        'views' => 'integer',
    ];

    /**
     * Increment the view count for this FAQ.
     */
    public function incrementViews(): void
    {
        $this->increment('views');
    }

    /**
     * Scope to get only active FAQs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to search in question and answer.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('question', 'like', '%' . $search . '%')
              ->orWhere('answer', 'like', '%' . $search . '%');
        });
    }
}

