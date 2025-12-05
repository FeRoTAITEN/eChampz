<?php

namespace App\Enums;

enum UserRole: string
{
    case GAMER = 'gamer';
    case RECRUITER = 'recruiter';

    /**
     * Get all role values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get role label
     */
    public function label(): string
    {
        return match($this) {
            self::GAMER => 'Gamer',
            self::RECRUITER => 'Recruiter',
        };
    }
}

