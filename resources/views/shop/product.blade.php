@extends('layouts.shop')

@section('title', $product->name)

@section('content')
    <div class="grid gap-10 lg:grid-cols-2">
        <div class="space-y-3">
            @php $primary = $product->images->first(); @endphp
            @if ($primary)
                <img src="{{ $primary->url() }}" class="w-full rounded-2xl border border-zinc-200 object-cover" alt="{{ $product->name }}" width="800" height="800" loading="eager">
            @endif
            <div class="grid grid-cols-4 gap-2">
                @foreach ($product->images->skip(1) as $image)
                    <img src="{{ $image->url() }}" class="h-20 w-full rounded-lg object-cover" alt="" width="80" height="80" loading="lazy">
                @endforeach
            </div>
        </div>
        <div>
            <p class="text-sm font-semibold text-emerald-800">{{ $product->category->name }}</p>
            <h1 class="mt-2 text-3xl font-bold text-zinc-900">{{ $product->name }}</h1>
            <p class="mt-3 text-base text-zinc-800">{{ $product->short_description }}</p>

            <form action="{{ route('shop.cart.add') }}" method="POST" class="mt-6 space-y-4 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-zinc-700">Variant</label>
                    <select name="product_variant_id" class="mt-1 w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 shadow-sm" required>
                        @foreach ($product->variants as $variant)
                            <option value="{{ $variant->id }}">{{ $variant->label() }} — ₹{{ number_format($variant->price, 2) }} ({{ $variant->stock }} in stock)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-zinc-700">Quantity</label>
                    <input type="number" name="quantity" value="1" min="1" class="mt-1 w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 shadow-sm">
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-zinc-950 hover:bg-brand-500">Add to cart</button>
                    @auth
                        <button type="submit" name="buy_now" value="1" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-900 hover:bg-zinc-50">Buy now</button>
                    @else
                        <a href="{{ route('login') }}" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-900 hover:bg-zinc-50">Login to buy</a>
                    @endauth
                </div>
            </form>

            <div class="prose prose-zinc mt-8 max-w-none">
                <h3 class="text-lg font-semibold text-zinc-900">Details</h3>
                <p class="whitespace-pre-line text-sm text-zinc-800">{{ $product->detailed_description }}</p>
            </div>
        </div>
    </div>
@endsection
