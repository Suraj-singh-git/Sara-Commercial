@extends('layouts.shop')

@section('title', 'Checkout')

@section('content')
    <h1 class="text-2xl font-bold text-zinc-900">Checkout</h1>
    <p class="mt-1 text-sm text-zinc-600">Review your items and enter shipping details.</p>

    <div class="mt-6 grid gap-8 lg:grid-cols-2">
        <form method="POST" action="{{ route('shop.checkout.store') }}" class="space-y-4 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
            @csrf
            <h2 class="text-lg font-semibold text-zinc-900">Shipping</h2>
            <div>
                <label class="text-xs font-semibold text-zinc-600">Full name</label>
                <input name="contact_name" value="{{ old('contact_name', auth()->user()->name) }}" class="mt-1 w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500/30" required>
            </div>
            <div>
                <label class="text-xs font-semibold text-zinc-600">Phone</label>
                <input name="phone" value="{{ old('phone', auth()->user()->phone) }}" class="mt-1 w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500/30" required>
            </div>
            <div>
                <label class="text-xs font-semibold text-zinc-600">Address line 1</label>
                <input name="line1" value="{{ old('line1') }}" class="mt-1 w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500/30" required>
            </div>
            <div>
                <label class="text-xs font-semibold text-zinc-600">Address line 2</label>
                <input name="line2" value="{{ old('line2') }}" class="mt-1 w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500/30">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-zinc-600">City</label>
                    <input id="checkout-city" name="city" value="{{ old('city') }}" class="mt-1 w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500/30" required>
                </div>
                <div>
                    <label class="text-xs font-semibold text-zinc-600">State</label>
                    <input id="checkout-state" name="state" value="{{ old('state') }}" class="mt-1 w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500/30">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-zinc-600">Postal code</label>
                    <input id="checkout-postal-code" name="postal_code" value="{{ old('postal_code') }}" class="mt-1 w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500/30" required>
                    <p id="checkout-postal-help" class="mt-1 text-[11px] text-zinc-500">Enter 6-digit Indian pincode to auto-fill city/state.</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-zinc-600">Country</label>
                    <input id="checkout-country" name="country" value="{{ old('country', 'IN') }}" maxlength="2" class="mt-1 w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500/30">
                </div>
            </div>

            <h2 class="pt-4 text-lg font-semibold text-zinc-900">Payment</h2>
            <select name="payment_mode" class="w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500/30" required>
                <option value="full_online">Pay online (Razorpay stub)</option>
                <option value="partial_cod">10% online + COD</option>
                <option value="cod">Cash on delivery</option>
            </select>

            <button type="submit" class="w-full rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-zinc-950 shadow-sm transition hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:ring-offset-2">Place order</button>
        </form>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-zinc-900">Order summary</h2>
            <ul class="mt-4 divide-y divide-zinc-100">
                @php $subtotal = 0; @endphp
                @forelse ($items as $item)
                    @php
                        $line = (float) $item->variant->price * $item->quantity;
                        $subtotal += $line;
                        $thumb = $item->variant->product->images->first();
                    @endphp
                    <li class="flex gap-4 py-4 first:pt-0">
                        <div class="shrink-0">
                            @if ($thumb)
                                <img src="{{ $thumb->url() }}" alt="" class="h-20 w-20 rounded-lg border border-zinc-100 object-cover" loading="lazy" width="80" height="80">
                            @else
                                <div class="flex h-20 w-20 items-center justify-center rounded-lg border border-dashed border-zinc-200 bg-zinc-50 text-[10px] font-medium text-zinc-400">No image</div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-zinc-900">{{ $item->variant->product->name }}</p>
                            <p class="mt-0.5 text-xs text-zinc-500">{{ $item->variant->label() }} · Qty {{ $item->quantity }}</p>
                            <p class="mt-2 text-sm font-bold text-brand-700">₹{{ number_format($line, 2) }}</p>
                        </div>
                    </li>
                @empty
                    <li class="py-6 text-sm text-zinc-600">
                        Your cart is empty. <a href="{{ route('shop.catalog') }}" class="font-semibold text-emerald-700 hover:text-emerald-800">Browse products</a>.
                    </li>
                @endforelse
            </ul>
            <div class="mt-4 flex items-center justify-between border-t border-zinc-200 pt-4 text-base font-bold text-zinc-900">
                <span>Total</span>
                <span>₹{{ number_format($subtotal, 2) }}</span>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const postalInput = document.getElementById('checkout-postal-code');
            const cityInput = document.getElementById('checkout-city');
            const stateInput = document.getElementById('checkout-state');
            const countryInput = document.getElementById('checkout-country');
            const helper = document.getElementById('checkout-postal-help');

            if (!postalInput || !cityInput || !stateInput || !countryInput || !helper) {
                return;
            }

            let requestCounter = 0;

            const setHelper = (text, kind = 'normal') => {
                helper.textContent = text;
                helper.className = 'mt-1 text-[11px] ' + (
                    kind === 'error' ? 'text-rose-600'
                        : kind === 'ok' ? 'text-emerald-700'
                        : 'text-zinc-500'
                );
            };

            const lookupPostal = async () => {
                const raw = postalInput.value || '';
                const pin = raw.replace(/\D/g, '');

                if (pin.length !== 6) {
                    setHelper('Enter 6-digit Indian pincode to auto-fill city/state.');
                    return;
                }

                const thisRequest = ++requestCounter;
                setHelper('Checking pincode...');

                try {
                    const res = await fetch(`https://api.postalpincode.in/pincode/${pin}`);
                    if (!res.ok) {
                        throw new Error('Network error');
                    }

                    const data = await res.json();
                    if (thisRequest !== requestCounter) {
                        return;
                    }

                    const row = Array.isArray(data) ? data[0] : null;
                    const office = row?.PostOffice?.[0];

                    if (!office) {
                        setHelper('Pincode not found. Please fill city/state manually.', 'error');
                        return;
                    }

                    if (!cityInput.value.trim()) {
                        cityInput.value = office.District || office.Block || '';
                    }
                    if (!stateInput.value.trim()) {
                        stateInput.value = office.State || '';
                    }
                    countryInput.value = 'IN';
                    setHelper('Address auto-filled from pincode.', 'ok');
                } catch (err) {
                    setHelper('Could not verify pincode right now. You can fill city/state manually.', 'error');
                }
            };

            postalInput.addEventListener('blur', lookupPostal);
            postalInput.addEventListener('change', lookupPostal);
        })();
    </script>
@endsection
