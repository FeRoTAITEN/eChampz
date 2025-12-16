<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $games = [
            [
                'name' => 'Valorant',
                'slug' => 'valorant',
                'icon_url' => null,
            ],
            [
                'name' => 'Counter-Strike 2',
                'slug' => 'counter-strike-2',
                'icon_url' => null,
            ],
            [
                'name' => 'League of Legends',
                'slug' => 'league-of-legends',
                'icon_url' => null,
            ],
            [
                'name' => 'Dota 2',
                'slug' => 'dota-2',
                'icon_url' => null,
            ],
            [
                'name' => 'Fortnite',
                'slug' => 'fortnite',
                'icon_url' => null,
            ],
            [
                'name' => 'Apex Legends',
                'slug' => 'apex-legends',
                'icon_url' => null,
            ],
            [
                'name' => 'Overwatch 2',
                'slug' => 'overwatch-2',
                'icon_url' => null,
            ],
            [
                'name' => 'Rocket League',
                'slug' => 'rocket-league',
                'icon_url' => null,
            ],
        ];

        foreach ($games as $game) {
            Game::updateOrCreate(
                ['slug' => $game['slug']],
                $game
            );
        }
    }
}


