<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Models\OtpCode;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OtpAuthService
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    public function requestOtp(string $phone, string $channel): string
    {
        $code = (string) random_int(100000, 999999);

        OtpCode::query()->create([
            'phone' => $phone,
            'channel' => $channel,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->notifications->otpSent($phone, $channel, $code);

        return $code;
    }

    public function verifyAndLogin(string $phone, string $code): User
    {
        $record = OtpCode::query()
            ->where('phone', $phone)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $record || ! Hash::check($code, $record->code_hash)) {
            throw ValidationException::withMessages(['code' => 'Invalid or expired OTP.']);
        }

        $record->update(['consumed_at' => now()]);

        $user = User::query()->firstOrCreate(
            ['phone' => $phone],
            [
                'name' => 'Customer '.$phone,
                'email' => 'user_'.$phone.'@customers.local',
                'password' => null,
                'role' => UserRole::Customer,
            ],
        );

        Auth::login($user);

        return $user;
    }
}
