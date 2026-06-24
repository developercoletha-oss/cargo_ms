<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_user_receives_password_reset_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        Notification::assertSentTo(
            $user,
            ResetPassword::class
        );
    }

    public function test_unknown_email_is_rejected_before_a_reset_is_sent(): void
    {
        Notification::fake();

        $this->from(route('password.request'))
            ->post(route('password.email'), [
                'email' => 'missing@example.com',
            ])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');

        Notification::assertNothingSent();
    }
}
