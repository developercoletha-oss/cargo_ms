<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_login_page_is_available_to_guests(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
    }

    public function test_authenticated_user_is_redirected_from_login_to_dashboard(): void
    {
        $user = new User([
            'full_name' => 'Test User',
            'email' => 'user@example.com',
        ]);

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect(route('dashboard.index'));
    }
}
