<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Update your name, contact details, and default shipping address.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="phone" :value="__('Phone')" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div class="border-t border-gray-100 pt-6 space-y-4">
            <h3 class="text-sm font-semibold text-gray-900">{{ __('Default address') }}</h3>

            <div>
                <x-input-label for="address_line1" :value="__('Address line 1')" />
                <x-text-input id="address_line1" name="address_line1" type="text" class="mt-1 block w-full" :value="old('address_line1', $defaultAddress->line1 ?? '')" />
                <x-input-error class="mt-2" :messages="$errors->get('address_line1')" />
            </div>

            <div>
                <x-input-label for="address_line2" :value="__('Address line 2')" />
                <x-text-input id="address_line2" name="address_line2" type="text" class="mt-1 block w-full" :value="old('address_line2', $defaultAddress->line2 ?? '')" />
                <x-input-error class="mt-2" :messages="$errors->get('address_line2')" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="address_city" :value="__('City')" />
                    <x-text-input id="address_city" name="address_city" type="text" class="mt-1 block w-full" :value="old('address_city', $defaultAddress->city ?? '')" />
                    <x-input-error class="mt-2" :messages="$errors->get('address_city')" />
                </div>
                <div>
                    <x-input-label for="address_state" :value="__('State')" />
                    <x-text-input id="address_state" name="address_state" type="text" class="mt-1 block w-full" :value="old('address_state', $defaultAddress->state ?? '')" />
                    <x-input-error class="mt-2" :messages="$errors->get('address_state')" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="address_postal_code" :value="__('Postal code')" />
                    <x-text-input id="address_postal_code" name="address_postal_code" type="text" class="mt-1 block w-full" :value="old('address_postal_code', $defaultAddress->postal_code ?? '')" />
                    <x-input-error class="mt-2" :messages="$errors->get('address_postal_code')" />
                </div>
                <div>
                    <x-input-label for="address_country" :value="__('Country (ISO)')" />
                    <x-text-input id="address_country" name="address_country" type="text" class="mt-1 block w-full" :value="old('address_country', $defaultAddress->country ?? 'IN')" maxlength="2" />
                    <x-input-error class="mt-2" :messages="$errors->get('address_country')" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
