@extends('layouts.shop')

@section('title', 'Cart')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Your cart</h1>
    <p class="mt-1 text-sm text-slate-600">Update quantities or remove items before checkout.</p>

    <div class="mt-6 space-y-4">
        @forelse ($items as $item)
            @php $thumb = $item->variant->product->images->first(); @endphp
            <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:gap-6">
                <div class="flex shrink-0 gap-4 sm:items-center">
                    @if ($thumb)
                        <img src="{{ $thumb->url() }}" alt="" class="h-24 w-24 rounded-lg border border-slate-100 object-cover sm:h-28 sm:w-28" loading="lazy" width="112" height="112">
                    @else
                        <div class="flex h-24 w-24 items-center justify-center rounded-lg border border-dashed border-slate-200 bg-slate-50 text-xs text-slate-400 sm:h-28 sm:w-28">No image</div>
                    @endif
                    <div class="min-w-0 flex-1 sm:hidden">
                        <p class="text-base font-semibold text-slate-900">{{ $item->variant->product->name }}</p>
                        <p class="text-sm text-slate-600">{{ $item->variant->label() }}</p>
                        <p class="text-sm font-semibold text-brand-700">₹{{ number_format($item->variant->price, 2) }} each</p>
                    </div>
                </div>
                <div class="min-w-0 flex-1 max-sm:hidden">
                    <p class="text-base font-semibold text-slate-900">{{ $item->variant->product->name }}</p>
                    <p class="text-sm text-slate-600">{{ $item->variant->label() }}</p>
                    <p class="mt-1 text-sm font-semibold text-brand-700">₹{{ number_format($item->variant->price, 2) }} each</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 sm:ml-auto sm:flex-nowrap">
                    <form method="POST" action="{{ route('shop.cart.update', $item) }}" class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <label class="sr-only" for="qty-{{ $item->id }}">Quantity</label>
                        <input id="qty-{{ $item->id }}" type="number" name="quantity" value="{{ $item->quantity }}" min="0" class="w-20 rounded-lg border-slate-200 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500/30">
                        <button type="submit" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-800 shadow-sm hover:bg-slate-50">Update</button>
                    </form>
                    <form method="POST" action="{{ route('shop.cart.remove', $item) }}" onsubmit="return confirm('Remove this item?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-700">Remove</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-600">Your cart is empty.</p>
        @endforelse
    </div>

    @auth
        @if ($items->isNotEmpty())
            <div class="mt-8">
                <a href="{{ route('shop.checkout') }}" class="inline-flex rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:ring-offset-2">Proceed to checkout</a>
            </div>
        @endif
    @else
        <p class="mt-6 text-sm text-slate-600">Please <a class="font-semibold text-brand-700 hover:text-brand-800" href="{{ route('login') }}">login</a> or use <a class="font-semibold text-brand-700 hover:text-brand-800" href="{{ route('otp.login') }}">OTP login</a> to checkout.</p>
    @endauth
@endsection
