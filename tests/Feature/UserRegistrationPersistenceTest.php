<?php

namespace Tests\Feature;

use App\Models\TransportStaff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRegistrationPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_persists_customer_user(): void
    {
        $response = $this->post(route('register.submit'), [
            'full_name' => 'New Customer',
            'phone' => '+255700111222',
            'email' => 'new-customer@example.test',
            'company_name' => 'New Customer Company',
            'address' => 'Dar es Salaam',
            'password' => 'test12345',
            'password_confirmation' => 'test12345',
        ]);

        $response->assertRedirect(route('login'));

        $user = User::query()->where('email', 'new-customer@example.test')->first();
        $this->assertNotNull($user);
        $this->assertSame('customer', $user->role);
        $this->assertFalse((bool) $user->is_active);
        $this->assertTrue(Hash::check('test12345', $user->password));
    }

    public function test_admin_created_transporter_persists_user_and_staff_record(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('dashboard.users.store'), [
            'full_name' => 'New Transporter',
            'email' => 'new-transporter@example.test',
            'phone' => '+255700333444',
            'role' => 'transporter',
            'company_name' => 'Fleet Company',
            'country' => 'TZ',
            'timezone' => 'Africa/Dar_es_Salaam',
            'is_active' => '1',
            'password' => 'test12345',
            'password_confirmation' => 'test12345',
            'address' => 'Arusha',
        ]);

        $response->assertRedirect(route('dashboard.users.index'));

        $user = User::query()->where('email', 'new-transporter@example.test')->first();
        $this->assertNotNull($user);
        $this->assertSame('transporter', $user->role);
        $this->assertTrue((bool) $user->is_active);
        $this->assertTrue(TransportStaff::query()->where('user_id', $user->id)->exists());
    }
}
