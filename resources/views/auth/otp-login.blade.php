<x-guest-layout>
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-zinc-900">OTP login</h2>
            <p class="mt-1 text-sm text-zinc-600">Login in 2 steps: send OTP, then verify code.</p>
        </div>

        <ol class="grid gap-2 text-xs sm:grid-cols-2">
            <li class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 font-semibold text-emerald-800">Step 1: Enter phone and send OTP</li>
            <li class="rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 font-semibold text-zinc-700">Step 2: Enter OTP and verify</li>
        </ol>

        @if (session('otp_preview'))
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                <p class="font-semibold">OTP preview (local/dev)</p>
                <p class="mt-1">Use code: <span class="rounded bg-white px-2 py-0.5 font-mono font-bold text-amber-700">{{ session('otp_preview') }}</span></p>
            </div>
        @endif

        @if (session('status'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('otp.send') }}" class="space-y-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
            @csrf
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Step 1</p>
            <div>
                <x-input-label for="phone" :value="__('Phone number')" />
                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full border-zinc-300" :value="old('phone', session('otp_phone'))" required />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="channel" :value="__('Channel')" />
                <select id="channel" name="channel" class="mt-1 block w-full rounded-md border-zinc-300 text-sm shadow-sm">
                    <option value="sms">SMS</option>
                    <option value="whatsapp">WhatsApp</option>
                </select>
            </div>
            <button type="submit" class="w-full rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-zinc-800">Send OTP</button>
        </form>

        <form method="POST" action="{{ route('otp.verify') }}" class="space-y-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
            @csrf
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Step 2</p>
            <div>
                <x-input-label for="vphone" :value="__('Phone')" />
                <x-text-input id="vphone" name="phone" type="text" class="mt-1 block w-full border-zinc-300" :value="old('phone', session('otp_phone'))" required />
            </div>
            <div>
                <x-input-label for="code" :value="__('OTP code')" />
                <x-text-input id="code" name="code" type="text" class="mt-1 block w-full border-zinc-300" required />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>
            <button type="submit" class="w-full rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Verify & login</button>
        </form>
    </div>
</x-guest-layout>
