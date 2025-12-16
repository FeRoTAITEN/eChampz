<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class MentionParser
{
    /**
     * Parse @mentions from text and return array of usernames.
     */
    public function extractMentions(string $text): array
    {
        // Regex: @ followed by word characters (letters, numbers, underscore)
        // Matches: @username, @user_name, @user123
        // Doesn't match: @user-name (hyphen), @user name (space)
        preg_match_all('/@(\w+)/', $text, $matches);
        
        return array_unique($matches[1] ?? []);
    }

    /**
     * Find all mention positions in text.
     * Returns: [['username' => 'john', 'position' => 4, 'length' => 9], ...]
     */
    public function findMentionPositions(string $text): array
    {
        $positions = [];
        $offset = 0;
        
        while (preg_match('/@(\w+)/', $text, $match, PREG_OFFSET_CAPTURE, $offset)) {
            $username = $match[1][0];
            $position = $match[0][1]; // Position of @ symbol
            $length = strlen($match[0][0]); // Length of "@username"
            
            $positions[] = [
                'username' => $username,
                'position' => $position,
                'length' => $length,
            ];
            
            $offset = $position + $length;
        }
        
        return $positions;
    }

    /**
     * Validate mentions and return valid user IDs.
     */
    public function validateMentions(array $usernames): Collection
    {
        return User::whereIn('username', $usernames)
            ->get()
            ->keyBy('username');
    }

    /**
     * Parse and validate mentions from text.
     * Returns: Collection of User models that were mentioned.
     */
    public function parseAndValidate(string $text): Collection
    {
        $usernames = $this->extractMentions($text);
        
        if (empty($usernames)) {
            return collect([]);
        }
        
        return $this->validateMentions($usernames);
    }
}


