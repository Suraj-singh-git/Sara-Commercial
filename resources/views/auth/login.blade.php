<x-guest-layout>
    @php
        $initialMode = ($errors->has('phone') || $errors->has('code') || session('otp_phone') || session('otp_preview')) ? 'otp' : 'password';
        $otpInitialStep = ($errors->has('code') || session('otp_phone')) ? 2 : 1;
    @endphp

    <div x-data="{ mode: '{{ $initialMode }}', otpStep: {{ $otpInitialStep }} }" class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Welcome back</h1>
            <p class="mt-2 text-sm text-zinc-600">Choose your login method.</p>
        </div>

        <div class="grid grid-cols-2 rounded-xl border border-zinc-200 bg-zinc-50 p-1">
            <button type="button" @click="mode = 'password'" :class="mode === 'password' ? 'bg-white text-zinc-900 shadow-sm' : 'text-zinc-600'" class="rounded-lg px-3 py-2 text-sm font-semibold transition">Password login</button>
            <button type="button" @click="mode = 'otp'" :class="mode === 'otp' ? 'bg-white text-zinc-900 shadow-sm' : 'text-zinc-600'" class="rounded-lg px-3 py-2 text-sm font-semibold transition">OTP login</button>
        </div>

        <x-auth-session-status class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800" :status="session('status')" />

        <div x-show="mode === 'password'" x-cloak class="space-y-5">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div class="rounded-xl border border-zinc-200 bg-zinc-50/60 p-4">
                    <x-input-label for="email" :value="__('Email')" class="text-zinc-700" />
                    <x-text-input id="email" class="mt-1.5 block w-full border-zinc-300 bg-zinc-50/50 text-zinc-900 placeholder:text-zinc-400 focus:border-amber-500 focus:ring-amber-500/30" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50/60 p-4">
                    <x-input-label for="password" :value="__('Password')" class="text-zinc-700" />
                    <x-text-input id="password" class="mt-1.5 block w-full border-zinc-300 bg-zinc-50/50 text-zinc-900 focus:border-amber-500 focus:ring-amber-500/30"
                        type="password"
                        name="password"
                        required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2">
                        <input id="remember_me" type="checkbox" class="rounded border-zinc-300 text-amber-600 shadow-sm focus:ring-amber-500/40" name="remember">
                        <span class="text-sm text-zinc-600">{{ __('Remember me') }}</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a class="text-sm font-semibold text-amber-700 hover:text-amber-800" href="{{ route('password.request') }}">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>

                <button type="submit" class="flex w-full items-center justify-center rounded-xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:ring-offset-2">
                    {{ __('Log in') }}
                </button>
            </form>
        </div>

        <div x-show="mode === 'otp'" x-cloak class="space-y-4">
            @if (session('otp_preview'))
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <p class="font-semibold">OTP preview (local/dev)</p>
                    <p class="mt-1">Use code: <span class="rounded bg-white px-2 py-0.5 font-mono font-bold text-amber-700">{{ session('otp_preview') }}</span></p>
                </div>
            @endif

            <form x-show="otpStep === 1" x-cloak method="POST" action="{{ route('otp.send') }}" class="space-y-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
                @csrf
                <input type="hidden" name="return_to" value="login">
                <div>
                    <x-input-label for="otp_phone" :value="__('Phone number')" />
                    <x-text-input id="otp_phone" name="phone" type="text" class="mt-1 block w-full border-zinc-300" :value="old('phone', session('otp_phone'))" required />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="otp_channel" :value="__('Channel')" />
                    <select id="otp_channel" name="channel" class="mt-1 block w-full rounded-md border-zinc-300 text-sm shadow-sm">
                        <option value="sms">SMS</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                </div>
                <button type="submit" class="w-full rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-zinc-800">Send OTP</button>
            </form>

            <form x-show="otpStep === 2" x-cloak method="POST" action="{{ route('otp.verify') }}" class="space-y-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
                @csrf
                <div>
                    <x-input-label for="otp_verify_phone" :value="__('Phone number')" />
                    <x-text-input id="otp_verify_phone" name="phone" type="text" class="mt-1 block w-full border-zinc-300" :value="old('phone', session('otp_phone'))" required />
                </div>
                <div>
                    <x-input-label for="otp_code" :value="__('OTP code')" />
                    <x-text-input id="otp_code" name="code" type="text" class="mt-1 block w-full border-zinc-300" required />
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>
                <button type="submit" class="w-full rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Verify & login</button>
                <button type="button" @click="otpStep = 1" class="w-full rounded-lg border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">Change phone / resend OTP</button>
            </form>
        </div>

        <p class="border-t border-zinc-100 pt-6 text-center text-sm text-zinc-600">
            New here?
            <a class="font-semibold text-amber-700 hover:text-amber-800" href="{{ route('register') }}">Create an account</a>
        </p>
    </div>
</x-guest-layout>
