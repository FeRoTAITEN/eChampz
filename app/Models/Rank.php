<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Rank extends Model
{
    protected $fillable = [
        'name',
        'label',
        'min_xp',
        'max_xp',
        'order',
    ];

    protected $casts = [
        'min_xp' => 'integer',
        'max_xp' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get rank for a given XP amount.
     */
    public static function getRankForXp(int $xp): ?self
    {
        return Cache::remember("rank_for_xp_{$xp}", 3600, function () use ($xp) {
            return self::where('min_xp', '<=', $xp)
                ->where(function ($query) use ($xp) {
                    $query->whereNull('max_xp')
                        ->orWhere('max_xp', '>=', $xp);
                })
                ->orderBy('order', 'desc')
                ->first();
        });
    }

    /**
     * Get all ranks ordered.
     */
    public static function getAllRanks(): \Illuminate\Support\Collection
    {
        return Cache::remember('all_ranks', 3600, function () {
            return self::orderBy('order')->get();
        });
    }
}
