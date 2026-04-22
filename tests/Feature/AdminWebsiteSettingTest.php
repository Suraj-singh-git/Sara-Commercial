<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminWebsiteSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_and_reset_website_settings(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Brand settings');

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'site_title' => 'Acme Industrial',
                'company_name' => 'Acme Co',
                'theme_color' => '#112233',
                'logo' => UploadedFile::fake()->image('logo.png', 120, 120),
            ])
            ->assertRedirect(route('admin.settings.edit'));

        $setting = WebsiteSetting::query()->firstOrFail();
        $this->assertSame('Acme Industrial', $setting->site_title);
        $this->assertSame('Acme Co', $setting->company_name);
        $this->assertSame('#112233', $setting->theme_color);
        $this->assertNotNull($setting->logo_path);
        Storage::disk('public')->assertExists((string) $setting->logo_path);

        $oldLogoPath = (string) $setting->logo_path;

        $this->actingAs($admin)
            ->post(route('admin.settings.reset'))
            ->assertRedirect(route('admin.settings.edit'));

        $setting->refresh();
        $this->assertSame(WebsiteSetting::DEFAULT_SITE_TITLE, $setting->site_title);
        $this->assertSame(WebsiteSetting::DEFAULT_COMPANY_NAME, $setting->company_name);
        $this->assertSame(WebsiteSetting::DEFAULT_THEME_COLOR, $setting->theme_color);
        $this->assertNull($setting->logo_path);
        Storage::disk('public')->assertMissing($oldLogoPath);
    }
}

