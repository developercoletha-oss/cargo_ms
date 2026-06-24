<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginLockoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_third_failed_attempt_locks_account_for_twenty_four_hours(): void
    {
        $user = $this->activeUser();

        $this->post(route('login.submit'), $this->credentials('wrong-one'))
            ->assertSessionHasErrors('email');
        $this->assertSame(1, $user->fresh()->failed_login_attempts);

        $this->post(route('login.submit'), $this->credentials('wrong-two'))
            ->assertSessionHasErrors('email');
        $this->assertSame(2, $user->fresh()->failed_login_attempts);

        $response = $this->post(route('login.submit'), $this->credentials('wrong-three'));

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('locked until', session('errors')->first('email'));

        $lockedUser = $user->fresh();

        $this->assertSame(3, $lockedUser->failed_login_attempts);
        $this->assertNotNull($lockedUser->login_locked_until);
        $this->assertTrue($lockedUser->login_locked_until->between(
            now()->addHours(23)->addMinutes(59),
            now()->addHours(24)->addMinute(),
        ));
    }

    public function test_locked_account_cannot_bypass_with_correct_password_new_session_or_new_ip(): void
    {
        $user = $this->activeUser([
            'failed_login_attempts' => 3,
            'login_locked_until' => now()->addHours(24),
        ]);

        $this->withSession([])
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->post(route('login.submit'), $this->credentials('correct-password'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->withSession([])
            ->withServerVariables(['REMOTE_ADDR' => '198.51.100.25'])
            ->post(route('login.submit'), [
                'email' => strtoupper($user->email),
                'password' => 'correct-password',
            ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(3, $user->fresh()->failed_login_attempts);
    }

    public function test_successful_login_clears_previous_failed_attempts(): void
    {
        $user = $this->activeUser([
            'failed_login_attempts' => 2,
        ]);

        $this->post(route('login.submit'), $this->credentials('correct-password'))
            ->assertRedirect(route('dashboard.index'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(0, $user->fresh()->failed_login_attempts);
        $this->assertNull($user->fresh()->login_locked_until);
    }

    public function test_account_can_login_after_full_lock_period_expires(): void
    {
        $user = $this->activeUser([
            'failed_login_attempts' => 3,
            'login_locked_until' => now()->subSecond(),
        ]);

        $this->post(route('login.submit'), $this->credentials('correct-password'))
            ->assertRedirect(route('dashboard.index'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(0, $user->fresh()->failed_login_attempts);
        $this->assertNull($user->fresh()->login_locked_until);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function activeUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'email' => 'locked-user@example.com',
            'password' => Hash::make('correct-password'),
            'role' => 'customer',
            'is_active' => true,
        ], $overrides));
    }

    /**
     * @return array<string, string>
     */
    private function credentials(string $password): array
    {
        return [
            'email' => 'locked-user@example.com',
            'password' => $password,
        ];
    }
}
