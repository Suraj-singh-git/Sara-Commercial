<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\OtpAuthService;
use App\Services\Notification\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OtpLoginController extends Controller
{
    public function __construct(
        private readonly OtpAuthService $otp,
        private readonly NotificationService $notifications,
    ) {}

    public function create(): View
    {
        return view('auth.otp-login');
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:32'],
            'channel' => ['required', 'in:sms,whatsapp'],
            'return_to' => ['nullable', 'string'],
        ]);

        $code = $this->otp->requestOtp($data['phone'], $data['channel']);
        $returnRoute = in_array($data['return_to'] ?? null, ['login', 'otp.login'], true)
            ? $data['return_to']
            : 'otp.login';

        $redirect = redirect()
            ->route($returnRoute)
            ->with('otp_phone', $data['phone'])
            ->with('status', 'OTP sent successfully.');

        if (app()->environment('local')) {
            $redirect->with('otp_preview', $code);
        }

        return $redirect;
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:32'],
            'code' => ['required', 'string', 'max:10'],
        ]);

        $this->otp->verifyAndLogin($data['phone'], $data['code']);

        if ($request->user()) {
            $this->notifications->userLoggedIn($request->user());
        }

        return redirect()->intended(route('shop.home', absolute: false));
    }
}
