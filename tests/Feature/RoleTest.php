<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can register as gamer
     */
    public function test_user_can_register_as_gamer(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Gamer User',
            'email' => 'gamer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'gamer',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'role' => 'gamer',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'gamer@example.com',
            'role' => 'gamer',
        ]);
    }

    /**
     * Test user can register as recruiter
     */
    public function test_user_can_register_as_recruiter(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Recruiter User',
            'email' => 'recruiter@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'recruiter',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'role' => 'recruiter',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'recruiter@example.com',
            'role' => 'recruiter',
        ]);
    }

    /**
     * Test user cannot register with invalid role
     */
    public function test_user_cannot_register_with_invalid_role(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test role is required for registration
     */
    public function test_role_is_required_for_registration(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    /**
     * Test get available roles endpoint
     */
    public function test_can_get_available_roles(): void
    {
        $response = $this->getJson('/api/v1/roles');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    ['value' => 'gamer', 'label' => 'Gamer'],
                    ['value' => 'recruiter', 'label' => 'Recruiter'],
                ],
            ]);
    }

    /**
     * Test user model isGamer method
     */
    public function test_user_is_gamer_method(): void
    {
        $gamer = User::factory()->gamer()->create();
        $recruiter = User::factory()->recruiter()->create();

        $this->assertTrue($gamer->isGamer());
        $this->assertFalse($gamer->isRecruiter());
        $this->assertFalse($recruiter->isGamer());
        $this->assertTrue($recruiter->isRecruiter());
    }

    /**
     * Test user model hasRole method
     */
    public function test_user_has_role_method(): void
    {
        $gamer = User::factory()->gamer()->create();

        $this->assertTrue($gamer->hasRole('gamer'));
        $this->assertTrue($gamer->hasRole(UserRole::GAMER));
        $this->assertFalse($gamer->hasRole('recruiter'));
        $this->assertFalse($gamer->hasRole(UserRole::RECRUITER));
    }

    /**
     * Test role is included in user response after login
     */
    public function test_role_is_included_in_login_response(): void
    {
        $user = User::factory()->gamer()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'role' => 'gamer',
                    ],
                ],
            ]);
    }
}

