<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Create your account</h1>
        <p class="mt-2 text-sm text-zinc-600">Join Sara Commercial to save carts, track deliveries, and reorder in one click.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Full name')" class="text-zinc-700" />
            <x-text-input id="name" class="mt-1.5 block w-full border-zinc-300 bg-zinc-50/50 text-zinc-900 focus:border-amber-500 focus:ring-amber-500/30" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" class="text-zinc-700" />
            <x-text-input id="email" class="mt-1.5 block w-full border-zinc-300 bg-zinc-50/50 text-zinc-900 focus:border-amber-500 focus:ring-amber-500/30" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="phone" :value="__('Phone (optional)')" class="text-zinc-700" />
            <x-text-input id="phone" class="mt-1.5 block w-full border-zinc-300 bg-zinc-50/50 text-zinc-900 focus:border-amber-500 focus:ring-amber-500/30" type="text" name="phone" :value="old('phone')" autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-1">
                <x-input-label for="password" :value="__('Password')" class="text-zinc-700" />
                <x-text-input id="password" class="mt-1.5 block w-full border-zinc-300 bg-zinc-50/50 text-zinc-900 focus:border-amber-500 focus:ring-amber-500/30"
                    type="password"
                    name="password"
                    required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div class="sm:col-span-1">
                <x-input-label for="password_confirmation" :value="__('Confirm password')" class="text-zinc-700" />
                <x-text-input id="password_confirmation" class="mt-1.5 block w-full border-zinc-300 bg-zinc-50/50 text-zinc-900 focus:border-amber-500 focus:ring-amber-500/30"
                    type="password"
                    name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <button type="submit" class="flex w-full items-center justify-center rounded-xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:ring-offset-2">
            {{ __('Register') }}
        </button>
    </form>

    <p class="mt-8 border-t border-zinc-100 pt-8 text-center text-sm text-zinc-600">
        Already have an account?
        <a class="font-semibold text-amber-700 hover:text-amber-800" href="{{ route('login') }}">{{ __('Log in') }}</a>
    </p>
</x-guest-layout>
