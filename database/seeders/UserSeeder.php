<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create specific test users
        $testUsers = [
            [
                'name' => 'John Doe',
                'username' => 'john_doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password'),
                'role' => UserRole::GAMER,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Jane Smith',
                'username' => 'jane_smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('password'),
                'role' => UserRole::GAMER,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Mike Johnson',
                'username' => 'mike_johnson',
                'email' => 'mike@example.com',
                'password' => Hash::make('password'),
                'role' => UserRole::GAMER,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Sarah Williams',
                'username' => 'sarah_williams',
                'email' => 'sarah@example.com',
                'password' => Hash::make('password'),
                'role' => UserRole::RECRUITER,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'David Brown',
                'username' => 'david_brown',
                'email' => 'david@example.com',
                'password' => Hash::make('password'),
                'role' => UserRole::RECRUITER,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Emily Davis',
                'username' => 'emily_davis',
                'email' => 'emily@example.com',
                'password' => Hash::make('password'),
                'role' => UserRole::GAMER,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Chris Wilson',
                'username' => 'chris_wilson',
                'email' => 'chris@example.com',
                'password' => Hash::make('password'),
                'role' => UserRole::GAMER,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Lisa Anderson',
                'username' => 'lisa_anderson',
                'email' => 'lisa@example.com',
                'password' => Hash::make('password'),
                'role' => UserRole::RECRUITER,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($testUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        // Create additional random users using factory
        User::factory()->count(10)->gamer()->create([
            'email_verified_at' => now(),
        ]);

        User::factory()->count(5)->recruiter()->create([
            'email_verified_at' => now(),
        ]);

        // Ensure all users have usernames
        User::whereNull('username')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $baseUsername = strtolower(str_replace(' ', '_', $user->name));
                $username = $baseUsername;
                $counter = 1;

                // Ensure unique username
                while (User::where('username', $username)->where('id', '!=', $user->id)->exists()) {
                    $username = $baseUsername . '_' . $counter;
                    $counter++;
                }

                $user->update(['username' => $username]);
            }
        });
    }
}


