<?php

namespace Tests\Feature;

use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test send verification code to unverified user
     */
    public function test_send_verification_code_to_unverified_user(): void
    {
        $user = User::factory()->unverified()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/email/send-verification');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['code'],
            ]);

        $this->assertDatabaseHas('email_verification_codes', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test cannot send verification code if already verified
     */
    public function test_cannot_send_code_if_already_verified(): void
    {
        $user = User::factory()->create(); // Factory creates verified users by default
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/email/send-verification');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Email is already verified.',
            ]);
    }

    /**
     * Test verify email with valid code
     */
    public function test_verify_email_with_valid_code(): void
    {
        $user = User::factory()->unverified()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Create verification code
        $code = '123456';
        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(30),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/email/verify', [
            'code' => $code,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email verified successfully',
            ]);

        // Check user is now verified
        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());

        // Check code is deleted
        $this->assertDatabaseMissing('email_verification_codes', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test verify email with invalid code
     */
    public function test_verify_email_with_invalid_code(): void
    {
        $user = User::factory()->unverified()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Create verification code
        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => '123456',
            'expires_at' => now()->addMinutes(30),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/email/verify', [
            'code' => '999999',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid verification code.',
            ]);
    }

    /**
     * Test verify email with expired code
     */
    public function test_verify_email_with_expired_code(): void
    {
        $user = User::factory()->unverified()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Create expired verification code
        $code = '123456';
        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->subMinutes(5),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/email/verify', [
            'code' => $code,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Verification code has expired. Please request a new one.',
            ]);
    }

    /**
     * Test cannot verify if already verified
     */
    public function test_cannot_verify_if_already_verified(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/email/verify', [
            'code' => '123456',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Email is already verified.',
            ]);
    }

    /**
     * Test get verification status
     */
    public function test_get_verification_status(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/email/status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'verified' => true,
                    'email' => $user->email,
                ],
            ]);
    }

    /**
     * Test unverified user cannot access verified routes
     */
    public function test_unverified_user_cannot_access_verified_routes(): void
    {
        $user = User::factory()->gamer()->unverified()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/gamer');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'requires_verification' => true,
            ]);
    }

    /**
     * Test verified user can access verified routes
     */
    public function test_verified_user_can_access_verified_routes(): void
    {
        $user = User::factory()->gamer()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Note: This will return 404 because no routes are defined inside the gamer group
        // But it proves the verified middleware passed
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/gamer');

        // Should not return 403 (forbidden due to unverified email)
        $this->assertNotEquals(403, $response->status());
    }

    /**
     * Test full email verification flow
     */
    public function test_full_email_verification_flow(): void
    {
        // Register a new user
        $registerResponse = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'gamer',
        ]);

        $registerResponse->assertStatus(201);
        $token = $registerResponse->json('data.token');

        // Check status - should not be verified
        $statusResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/email/status');

        $statusResponse->assertJson([
            'data' => ['verified' => false],
        ]);

        // Send verification code
        $sendResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/email/send-verification');

        $sendResponse->assertStatus(200);
        $code = $sendResponse->json('data.code');

        // Verify email
        $verifyResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/email/verify', [
            'code' => $code,
        ]);

        $verifyResponse->assertStatus(200);

        // Check status - should be verified now
        $statusResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/email/status');

        $statusResponse->assertJson([
            'data' => ['verified' => true],
        ]);
    }
}

