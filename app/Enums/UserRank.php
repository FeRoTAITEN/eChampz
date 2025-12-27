<?php

namespace App\Enums;

enum UserRank: string
{
    case BRONZE = 'bronze';
    case SILVER = 'silver';
    case GOLD = 'gold';

    /**
     * Get label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::BRONZE => 'Bronze',
            self::SILVER => 'Silver',
            self::GOLD => 'Gold',
        };
    }

    /**
     * Get minimum XP required for this rank.
     */
    public function minXp(): int
    {
        return match ($this) {
            self::BRONZE => 0,
            self::SILVER => 100,
            self::GOLD => 500,
        };
    }

    /**
     * Get rank from XP amount.
     */
    public static function fromXp(int $xp): self
    {
        if ($xp >= self::GOLD->minXp()) {
            return self::GOLD;
        }
        
        if ($xp >= self::SILVER->minXp()) {
            return self::SILVER;
        }
        
        return self::BRONZE;
    }

    /**
     * Get all ranks with their XP thresholds.
     */
    public static function thresholds(): array
    {
        return [
            'bronze' => ['min' => 0, 'max' => 99],
            'silver' => ['min' => 100, 'max' => 499],
            'gold' => ['min' => 500, 'max' => null],
        ];
    }
}
