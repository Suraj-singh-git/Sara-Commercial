<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OtpLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_otp_code(): void
    {
        $response = $this->post(route('otp.send'), [
            'phone' => '9990001111',
            'channel' => 'sms',
        ]);

        $response->assertRedirect(route('otp.login'));
        $this->assertDatabaseHas('otp_codes', [
            'phone' => '9990001111',
            'channel' => 'sms',
        ]);
    }

    public function test_user_can_verify_otp_and_login(): void
    {
        OtpCode::query()->create([
            'phone' => '9990002222',
            'channel' => 'sms',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->post(route('otp.verify'), [
            'phone' => '9990002222',
            'code' => '123456',
        ]);

        $response->assertRedirect(route('shop.home', absolute: false));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'phone' => '9990002222',
            'role' => UserRole::Customer->value,
        ]);
    }

    public function test_expired_or_invalid_otp_is_rejected(): void
    {
        User::factory()->create();

        OtpCode::query()->create([
            'phone' => '9990003333',
            'channel' => 'sms',
            'code_hash' => Hash::make('654321'),
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->from(route('otp.login'))->post(route('otp.verify'), [
            'phone' => '9990003333',
            'code' => '654321',
        ]);

        $response->assertRedirect(route('otp.login'));
        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }
}

