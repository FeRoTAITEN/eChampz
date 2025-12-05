<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test forgot password sends reset code
     */
    public function test_forgot_password_sends_reset_code(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/password/forgot', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['code'], // Only in testing/local environment
            ]);

        // Check token was created in database
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    /**
     * Test forgot password fails with non-existent email
     */
    public function test_forgot_password_fails_with_nonexistent_email(): void
    {
        $response = $this->postJson('/api/v1/password/forgot', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test verify code with valid code
     */
    public function test_verify_code_with_valid_code(): void
    {
        $user = User::factory()->create();
        $code = '123456';

        // Create a password reset token
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($code),
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/password/verify-code', [
            'email' => $user->email,
            'code' => $code,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['reset_token'],
            ]);
    }

    /**
     * Test verify code fails with invalid code
     */
    public function test_verify_code_fails_with_invalid_code(): void
    {
        $user = User::factory()->create();

        // Create a password reset token
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/password/verify-code', [
            'email' => $user->email,
            'code' => '999999',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid reset code.',
            ]);
    }

    /**
     * Test verify code fails with expired code
     */
    public function test_verify_code_fails_with_expired_code(): void
    {
        $user = User::factory()->create();
        $code = '123456';

        // Create an expired password reset token (70 minutes ago)
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($code),
            'created_at' => now()->subMinutes(70),
        ]);

        $response = $this->postJson('/api/v1/password/verify-code', [
            'email' => $user->email,
            'code' => $code,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Reset code has expired. Please request a new one.',
            ]);
    }

    /**
     * Test reset password with valid token
     */
    public function test_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create();
        $resetToken = 'valid_reset_token_123';

        // Create a password reset token
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($resetToken),
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/password/reset', [
            'email' => $user->email,
            'reset_token' => $resetToken,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password has been reset successfully. Please login with your new password.',
            ]);

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));

        // Verify token was deleted
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    /**
     * Test reset password fails with invalid token
     */
    public function test_reset_password_fails_with_invalid_token(): void
    {
        $user = User::factory()->create();

        // Create a password reset token
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('valid_token'),
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/password/reset', [
            'email' => $user->email,
            'reset_token' => 'invalid_token',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid reset token.',
            ]);
    }

    /**
     * Test reset password fails with expired token
     */
    public function test_reset_password_fails_with_expired_token(): void
    {
        $user = User::factory()->create();
        $resetToken = 'valid_reset_token';

        // Create an expired password reset token (20 minutes ago)
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($resetToken),
            'created_at' => now()->subMinutes(20),
        ]);

        $response = $this->postJson('/api/v1/password/reset', [
            'email' => $user->email,
            'reset_token' => $resetToken,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Reset token has expired. Please start over.',
            ]);
    }

    /**
     * Test reset password revokes all existing tokens
     */
    public function test_reset_password_revokes_all_tokens(): void
    {
        $user = User::factory()->create();
        $resetToken = 'valid_reset_token';

        // Create some auth tokens
        $user->createToken('device1');
        $user->createToken('device2');

        $this->assertCount(2, $user->tokens);

        // Create a password reset token
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($resetToken),
            'created_at' => now(),
        ]);

        $this->postJson('/api/v1/password/reset', [
            'email' => $user->email,
            'reset_token' => $resetToken,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        // Verify all tokens were revoked
        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }

    /**
     * Test full password reset flow
     */
    public function test_full_password_reset_flow(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        // Step 1: Request reset code
        $forgotResponse = $this->postJson('/api/v1/password/forgot', [
            'email' => $user->email,
        ]);

        $forgotResponse->assertStatus(200);
        $code = $forgotResponse->json('data.code');

        // Step 2: Verify code and get reset token
        $verifyResponse = $this->postJson('/api/v1/password/verify-code', [
            'email' => $user->email,
            'code' => $code,
        ]);

        $verifyResponse->assertStatus(200);
        $resetToken = $verifyResponse->json('data.reset_token');

        // Step 3: Reset password
        $resetResponse = $this->postJson('/api/v1/password/reset', [
            'email' => $user->email,
            'reset_token' => $resetToken,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $resetResponse->assertStatus(200);

        // Step 4: Verify can login with new password
        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'newpassword123',
        ]);

        $loginResponse->assertStatus(200);
    }
}

