<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Game;
use App\Models\Post;
use App\Models\Rank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruiterToolsTest extends TestCase
{
    use RefreshDatabase;

    private User $recruiter;
    private User $bronzeGamer;
    private User $silverGamer;
    private User $goldGamer;
    private User $unverifiedGamer;
    private Game $game1;
    private Game $game2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create ranks (they're seeded in migration, but ensure they exist)
        Rank::firstOrCreate(['name' => 'bronze'], [
            'label' => 'Bronze',
            'min_xp' => 0,
            'max_xp' => 99,
            'order' => 1,
        ]);
        Rank::firstOrCreate(['name' => 'silver'], [
            'label' => 'Silver',
            'min_xp' => 100,
            'max_xp' => 499,
            'order' => 2,
        ]);
        Rank::firstOrCreate(['name' => 'gold'], [
            'label' => 'Gold',
            'min_xp' => 500,
            'max_xp' => null,
            'order' => 3,
        ]);

        // Create recruiter
        $this->recruiter = User::factory()->recruiter()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);

        // Create gamers with different ranks
        $this->bronzeGamer = User::factory()->gamer()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
            'xp_total' => 50, // Bronze rank
        ]);

        $this->silverGamer = User::factory()->gamer()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
            'xp_total' => 250, // Silver rank
        ]);

        $this->goldGamer = User::factory()->gamer()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
            'xp_total' => 600, // Gold rank
        ]);

        // Create unverified gamer (should not appear in results)
        $this->unverifiedGamer = User::factory()->gamer()->create([
            'email_verified_at' => null,
            'onboarding_completed_at' => null,
            'xp_total' => 100,
        ]);

        // Create games
        $this->game1 = Game::create([
            'name' => 'Valorant',
            'slug' => 'valorant',
        ]);

        $this->game2 = Game::create([
            'name' => 'Counter-Strike 2',
            'slug' => 'counter-strike-2',
        ]);

        // Add favorite games
        $this->bronzeGamer->favoriteGames()->attach($this->game1->id);
        $this->silverGamer->favoriteGames()->attach([$this->game1->id, $this->game2->id]);
        $this->goldGamer->favoriteGames()->attach($this->game2->id);
    }

    private function getAuthToken(User $user): string
    {
        return $user->createToken('test-token')->plainTextToken;
    }

    /**
     * Test recruiter can search all gamers
     */
    public function test_recruiter_can_search_all_gamers(): void
    {
        $token = $this->getAuthToken($this->recruiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/recruiter/search');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'username',
                            'avatar_url',
                            'xp_total',
                            'rank',
                            'favorite_games',
                        ],
                    ],
                ],
            ]);

        // Should return 3 verified gamers (not unverified)
        $data = $response->json('data.data');
        $this->assertCount(3, $data);
        $this->assertNotContains($this->unverifiedGamer->id, array_column($data, 'id'));
    }

    /**
     * Test recruiter can search by game
     */
    public function test_recruiter_can_search_by_game(): void
    {
        $token = $this->getAuthToken($this->recruiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/v1/recruiter/search?game_id={$this->game1->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data.data');
        $gamerIds = array_column($data, 'id');
        
        // Should return bronze and silver gamers (they favorited game1)
        $this->assertContains($this->bronzeGamer->id, $gamerIds);
        $this->assertContains($this->silverGamer->id, $gamerIds);
        $this->assertNotContains($this->goldGamer->id, $gamerIds);
    }

    /**
     * Test recruiter can search by rank - bronze
     */
    public function test_recruiter_can_search_by_rank_bronze(): void
    {
        $token = $this->getAuthToken($this->recruiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/recruiter/search?rank=bronze');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data.data');
        $gamerIds = array_column($data, 'id');
        
        // Should return only bronze gamer
        $this->assertContains($this->bronzeGamer->id, $gamerIds);
        $this->assertNotContains($this->silverGamer->id, $gamerIds);
        $this->assertNotContains($this->goldGamer->id, $gamerIds);
    }

    /**
     * Test recruiter can search by rank - silver
     */
    public function test_recruiter_can_search_by_rank_silver(): void
    {
        $token = $this->getAuthToken($this->recruiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/recruiter/search?rank=silver');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data.data');
        $gamerIds = array_column($data, 'id');
        
        // Should return only silver gamer
        $this->assertContains($this->silverGamer->id, $gamerIds);
        $this->assertNotContains($this->bronzeGamer->id, $gamerIds);
        $this->assertNotContains($this->goldGamer->id, $gamerIds);
    }

    /**
     * Test recruiter can search by rank - gold
     */
    public function test_recruiter_can_search_by_rank_gold(): void
    {
        $token = $this->getAuthToken($this->recruiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/recruiter/search?rank=gold');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data.data');
        $gamerIds = array_column($data, 'id');
        
        // Should return only gold gamer
        $this->assertContains($this->goldGamer->id, $gamerIds);
        $this->assertNotContains($this->bronzeGamer->id, $gamerIds);
        $this->assertNotContains($this->silverGamer->id, $gamerIds);
    }

    /**
     * Test recruiter can search by game and rank combined
     */
    public function test_recruiter_can_search_by_game_and_rank(): void
    {
        $token = $this->getAuthToken($this->recruiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/v1/recruiter/search?game_id={$this->game1->id}&rank=silver");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data.data');
        $gamerIds = array_column($data, 'id');
        
        // Should return only silver gamer who favorited game1
        $this->assertContains($this->silverGamer->id, $gamerIds);
        $this->assertNotContains($this->bronzeGamer->id, $gamerIds);
        $this->assertNotContains($this->goldGamer->id, $gamerIds);
    }

    /**
     * Test search validates rank parameter
     */
    public function test_search_validates_rank_parameter(): void
    {
        $token = $this->getAuthToken($this->recruiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/recruiter/search?rank=invalid');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test recruiter can get gamer cards
     */
    public function test_recruiter_can_get_gamer_cards(): void
    {
        $token = $this->getAuthToken($this->recruiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/recruiter/gamer-cards');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'username',
                            'avatar_url',
                            'xp_total',
                            'rank',
                        ],
                    ],
                ],
            ]);

        // Should return 3 verified gamers
        $data = $response->json('data.data');
        $this->assertCount(3, $data);
        
        // Should be ordered by XP descending
        $xps = array_column($data, 'xp_total');
        $this->assertEquals([600, 250, 50], $xps);
    }

    /**
     * Test gamer cards pagination
     */
    public function test_gamer_cards_pagination(): void
    {
        $token = $this->getAuthToken($this->recruiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/recruiter/gamer-cards?per_page=2&page=1');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(2, $data);
        $this->assertEquals(600, $data[0]['xp_total']);
        $this->assertEquals(250, $data[1]['xp_total']);
    }

    /**
     * Test recruiter can view full gamer profile
     */
    public function test_recruiter_can_view_full_gamer_profile(): void
    {
        $token = $this->getAuthToken($this->recruiter);

        // Create a post for the gamer
        Post::create([
            'user_id' => $this->silverGamer->id,
            'content' => 'Test post content',
            'type' => 'text',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/v1/recruiter/gamer-profile/{$this->silverGamer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->silverGamer->id,
                    'name' => $this->silverGamer->name,
                    'username' => $this->silverGamer->username,
                    'xp_total' => 250,
                    'rank' => 'silver',
                ],
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'username',
                    'avatar_url',
                    'xp_total',
                    'rank',
                    'favorite_games',
                    'platform_games',
                    'recent_posts_count',
                    'recent_posts',
                ],
            ]);
    }

    /**
     * Test recruiter cannot view profile of non-existent gamer
     */
    public function test_recruiter_cannot_view_profile_of_nonexistent_gamer(): void
    {
        $token = $this->getAuthToken($this->recruiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/recruiter/gamer-profile/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Gamer not found',
            ]);
    }

    /**
     * Test recruiter can get contact link
     */
    public function test_recruiter_can_get_contact_link(): void
    {
        $token = $this->getAuthToken($this->recruiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/v1/recruiter/contact-link/{$this->silverGamer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->silverGamer->id,
                    'name' => $this->silverGamer->name,
                    'username' => $this->silverGamer->username,
                    'email' => $this->silverGamer->email,
                ],
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'username',
                    'avatar_url',
                    'email',
                    'organization_name',
                    'position',
                    'represent_type',
                    'profile_url',
                ],
            ]);
    }

    /**
     * Test recruiter cannot get contact link of non-existent gamer
     */
    public function test_recruiter_cannot_get_contact_link_of_nonexistent_gamer(): void
    {
        $token = $this->getAuthToken($this->recruiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/recruiter/contact-link/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Gamer not found',
            ]);
    }

    /**
     * Test gamer cannot access recruiter tools
     */
    public function test_gamer_cannot_access_recruiter_tools(): void
    {
        $token = $this->getAuthToken($this->bronzeGamer);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/recruiter/search');

        $response->assertStatus(403);
    }

    /**
     * Test unauthenticated user cannot access recruiter tools
     */
    public function test_unauthenticated_user_cannot_access_recruiter_tools(): void
    {
        $response = $this->getJson('/api/v1/recruiter/search');

        $response->assertStatus(401);
    }

    /**
     * Test recruiter without verified email cannot access tools
     */
    public function test_recruiter_without_verified_email_cannot_access_tools(): void
    {
        $unverifiedRecruiter = User::factory()->recruiter()->create([
            'email_verified_at' => null,
        ]);

        $token = $this->getAuthToken($unverifiedRecruiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/recruiter/search');

        $response->assertStatus(403);
    }

    /**
     * Test search returns gamers ordered by XP descending
     */
    public function test_search_returns_gamers_ordered_by_xp_descending(): void
    {
        $token = $this->getAuthToken($this->recruiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/recruiter/search');

        $data = $response->json('data.data');
        $xps = array_column($data, 'xp_total');
        
        // Should be in descending order
        $this->assertEquals([600, 250, 50], $xps);
    }

    /**
     * Test gamer cards include correct rank
     */
    public function test_gamer_cards_include_correct_rank(): void
    {
        $token = $this->getAuthToken($this->recruiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/recruiter/gamer-cards');

        $data = $response->json('data.data');
        
        // Find each gamer in response
        $bronzeData = collect($data)->firstWhere('id', $this->bronzeGamer->id);
        $silverData = collect($data)->firstWhere('id', $this->silverGamer->id);
        $goldData = collect($data)->firstWhere('id', $this->goldGamer->id);

        $this->assertEquals('bronze', $bronzeData['rank']);
        $this->assertEquals('silver', $silverData['rank']);
        $this->assertEquals('gold', $goldData['rank']);
    }

    /**
     * Test full profile includes favorite games
     */
    public function test_full_profile_includes_favorite_games(): void
    {
        $token = $this->getAuthToken($this->recruiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/v1/recruiter/gamer-profile/{$this->silverGamer->id}");

        $favoriteGames = $response->json('data.favorite_games');
        
        $this->assertIsArray($favoriteGames);
        $this->assertCount(2, $favoriteGames); // Silver gamer has 2 favorite games
        
        $gameIds = array_column($favoriteGames, 'id');
        $this->assertContains($this->game1->id, $gameIds);
        $this->assertContains($this->game2->id, $gameIds);
    }
}

