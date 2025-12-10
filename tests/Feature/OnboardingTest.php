<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    private const XP_NAME = 10;
    private const XP_BIRTHDAY = 10;
    private const XP_REPRESENT = 15;
    private const XP_COMPLETE = 20;

    public function test_gamer_onboarding_completes_and_awards_xp(): void
    {
        $user = User::factory()->create([
            'role' => 'gamer',
            'name' => 'Old Name',
            'date_of_birth' => null,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Save name
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/onboarding/name', [
            'name' => 'New Name',
        ])->assertStatus(200);

        // Save birthday
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/onboarding/birthday', [
            'day' => 1,
            'month' => 1,
            'year' => 2000,
        ])->assertStatus(200);

        $user->refresh();

        $expectedXp = self::XP_NAME + self::XP_BIRTHDAY + self::XP_COMPLETE;

        $this->assertNotNull($user->date_of_birth);
        $this->assertNotNull($user->onboarding_completed_at, 'Onboarding should be completed for gamer after required steps.');
        $this->assertSame($expectedXp, $user->xp_total, 'XP total should include steps and completion bonus.');

        // Status should reflect completion
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/onboarding/status')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'completed' => true,
                    'pending_steps' => [],
                    'role' => 'gamer',
                    'xp_total' => $expectedXp,
                ],
            ]);
    }

    public function test_recruiter_onboarding_requires_represent_and_awards_xp_once(): void
    {
        $user = User::factory()->create([
            'role' => 'recruiter',
            'name' => 'Recruiter',
            'date_of_birth' => null,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Name step
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/onboarding/name', [
            'name' => 'Recruiter New',
        ])->assertStatus(200);

        // Birthday step
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/onboarding/birthday', [
            'day' => 15,
            'month' => 6,
            'year' => 1995,
        ])->assertStatus(200);

        // Represent step (organization)
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/onboarding/represent', [
            'type' => 'organization',
            'organization_name' => 'Team X',
            'position' => 'Manager',
        ])->assertStatus(200);

        // Calling represent again should not add XP
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/onboarding/represent', [
            'type' => 'organization',
            'organization_name' => 'Team X',
            'position' => 'Manager',
        ])->assertStatus(200);

        $user->refresh();

        $expectedXp = self::XP_NAME + self::XP_BIRTHDAY + self::XP_REPRESENT + self::XP_COMPLETE;

        $this->assertNotNull($user->date_of_birth);
        $this->assertSame('organization', $user->represent_type);
        $this->assertNotNull($user->onboarding_completed_at, 'Onboarding should complete after represent for recruiter.');
        $this->assertSame($expectedXp, $user->xp_total, 'XP should not double count repeated represent calls.');

        // Status should reflect no pending steps
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/onboarding/status')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'completed' => true,
                    'pending_steps' => [],
                    'role' => 'recruiter',
                    'xp_total' => $expectedXp,
                ],
            ]);
    }
}
