<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed users first (needed for posts)
        $this->call([
            UserSeeder::class,
        ]);

        // Seed admin panel data
        $this->call([
            PermissionSeeder::class,
            AdminSeeder::class,
            GameSeeder::class,
            PostSeeder::class,
        ]);
    }
}
