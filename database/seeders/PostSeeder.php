<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\User;
use App\Services\MentionParser;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get specific test users from UserSeeder by username
        $testUsernames = [
            'john_doe',
            'jane_smith',
            'mike_johnson',
            'sarah_williams',
            'david_brown',
            'emily_davis',
            'chris_wilson',
            'lisa_anderson',
        ];

        $users = User::whereIn('username', $testUsernames)
            ->whereNotNull('email_verified_at')
            ->get()
            ->keyBy('username');

        // If test users don't exist, get any users with usernames
        if ($users->isEmpty()) {
            $users = User::whereNotNull('username')
                ->whereNotNull('email_verified_at')
                ->limit(8)
                ->get()
                ->keyBy('username');
        }

        // Ensure we have at least 5 users for posts
        if ($users->count() < 5) {
            $additionalUsers = User::factory()->count(5 - $users->count())->create([
                'email_verified_at' => now(),
            ]);
            foreach ($additionalUsers as $user) {
                if (!$user->username) {
                    $baseUsername = strtolower(str_replace(' ', '_', $user->name));
                    $username = $baseUsername;
                    $counter = 1;
                    while (User::where('username', $username)->where('id', '!=', $user->id)->exists()) {
                        $username = $baseUsername . '_' . $counter;
                        $counter++;
                    }
                    $user->update(['username' => $username]);
                }
                $users->put($user->username, $user);
            }
        }

        // Get games
        $games = Game::all();
        if ($games->isEmpty()) {
            $this->call(GameSeeder::class);
            $games = Game::all();
        }

        // Get specific users for posts (prioritize test users)
        $john = $users->get('john_doe') ?? $users->first();
        $jane = $users->get('jane_smith') ?? $users->skip(1)->first() ?? $users->first();
        $mike = $users->get('mike_johnson') ?? $users->skip(2)->first() ?? $users->first();
        $sarah = $users->get('sarah_williams') ?? $users->skip(3)->first() ?? $users->first();
        $emily = $users->get('emily_davis') ?? $users->skip(4)->first() ?? $users->first();

        // Create sample posts using specific test users from UserSeeder
        $posts = [
            [
                'user_id' => $john->id,
                'content' => 'Just had an amazing clutch! Check out this play! @' . $jane->username,
                'type' => 'image',
                'game_ids' => [$games->where('slug', 'valorant')->first()->id],
            ],
            [
                'user_id' => $jane->id,
                'content' => 'Looking for a team to play with. @' . $mike->username . ' and @' . $sarah->username . ' interested?',
                'type' => 'text',
                'game_ids' => [$games->where('slug', 'counter-strike-2')->first()->id],
            ],
            [
                'user_id' => $mike->id,
                'content' => 'New strategy guide coming soon! Stay tuned.',
                'type' => 'text',
                'game_ids' => [$games->where('slug', 'league-of-legends')->first()->id, $games->where('slug', 'dota-2')->first()->id],
            ],
            [
                'user_id' => $sarah->id,
                'content' => 'Epic win streak today! Thanks @' . $john->username . ' for the support!',
                'type' => 'video',
                'game_ids' => [$games->where('slug', 'fortnite')->first()->id],
            ],
            [
                'user_id' => $emily->id,
                'content' => 'Just started streaming. Follow for daily content!',
                'type' => 'text',
                'game_ids' => [$games->where('slug', 'apex-legends')->first()->id],
            ],
        ];

        $mentionParser = new MentionParser();

        foreach ($posts as $postData) {
            $gameIds = $postData['game_ids'];
            $content = $postData['content'];
            unset($postData['game_ids']);

            $post = Post::create($postData);

            // Process mentions
            $positions = $mentionParser->findMentionPositions($content);
            if (!empty($positions)) {
                $usernames = array_column($positions, 'username');
                $validUsers = $mentionParser->validateMentions($usernames);

                foreach ($positions as $mentionData) {
                    $user = $validUsers->get($mentionData['username']);
                    if ($user) {
                        $post->mentions()->create([
                            'user_id' => $user->id,
                            'position' => $mentionData['position'],
                            'length' => $mentionData['length'],
                        ]);
                    }
                }
            }

            // Attach games
            $post->games()->attach($gameIds);

            // Add some media for image/video posts
            if (in_array($post->type, ['image', 'video'])) {
                PostMedia::create([
                    'post_id' => $post->id,
                    'type' => $post->type,
                    'url' => 'https://example.com/' . $post->type . '/' . $post->id . '.jpg',
                    'thumbnail_url' => $post->type === 'video' ? 'https://example.com/thumb/' . $post->id . '.jpg' : null,
                    'order' => 0,
                ]);
            }

            // Set some random engagement metrics
            $post->update([
                'views' => fake()->numberBetween(100, 50000),
                'upvotes' => fake()->numberBetween(0, 1000),
                'downvotes' => fake()->numberBetween(0, 100),
                'shares' => fake()->numberBetween(0, 500),
            ]);
        }
    }
}

