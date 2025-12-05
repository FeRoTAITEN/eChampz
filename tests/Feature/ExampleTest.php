<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test API health check endpoint
     */
    public function test_api_health_check_returns_successful_response(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    /**
     * Test API requires authentication for protected routes
     */
    public function test_protected_routes_require_authentication(): void
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401);
    }
}
