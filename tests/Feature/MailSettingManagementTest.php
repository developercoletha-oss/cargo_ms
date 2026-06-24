<?php

namespace Tests\Feature;

use App\Models\MailSetting;
use App\Models\User;
use App\Services\MailSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MailSettingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_activate_update_and_delete_mail_settings(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $first = MailSetting::create($this->settingData([
            'name' => 'Old SMTP',
            'is_active' => true,
        ]));

        $response = $this->actingAs($admin)->post(route('dashboard.mail_settings.store'), [
            ...$this->settingData([
                'name' => 'Primary SMTP',
                'password' => 'smtp-secret',
            ]),
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('dashboard.mail_settings.content'));

        $created = MailSetting::query()->where('name', 'Primary SMTP')->firstOrFail();

        $this->assertFalse($first->fresh()->is_active);
        $this->assertTrue($created->is_active);
        $this->assertSame('smtp-secret', $created->password);
        $this->assertNotSame('smtp-secret', DB::table('mail_settings')->where('id', $created->id)->value('password'));

        $this->actingAs($admin)
            ->put(route('dashboard.mail_settings.update', $created), [
                ...$this->settingData([
                    'name' => 'Updated SMTP',
                    'password' => '',
                ]),
                'is_active' => '1',
            ])
            ->assertRedirect(route('dashboard.mail_settings.content'));

        $this->assertSame('Updated SMTP', $created->fresh()->name);
        $this->assertSame('smtp-secret', $created->fresh()->password);

        $this->actingAs($admin)
            ->get(route('dashboard.mail_settings.content'))
            ->assertOk();

        $this->actingAs($admin)
            ->delete(route('dashboard.mail_settings.destroy', $created))
            ->assertRedirect(route('dashboard.mail_settings.content'));

        $this->assertDatabaseMissing('mail_settings', ['id' => $created->id]);
    }

    public function test_non_admin_cannot_manage_mail_settings(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->get(route('dashboard.mail_settings.content'))
            ->assertForbidden();
    }

    public function test_service_applies_active_password_reset_profile(): void
    {
        $setting = MailSetting::create($this->settingData([
            'password' => 'smtp-secret',
            'is_active' => true,
        ]));

        app(MailSettingService::class)->applyActiveSetting('password_reset');

        $this->assertSame('smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame(587, config('mail.mailers.smtp.port'));
        $this->assertSame('smtp-secret', config('mail.mailers.smtp.password'));
        $this->assertSame('noreply@cftms.test', config('mail.from.address'));
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function settingData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test SMTP',
            'purpose' => 'password_reset',
            'mailer' => 'smtp',
            'scheme' => 'smtp',
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'mailer@example.com',
            'password' => 'secret-password',
            'from_address' => 'noreply@cftms.test',
            'from_name' => 'CFTMS',
            'is_active' => false,
        ], $overrides);
    }
}
