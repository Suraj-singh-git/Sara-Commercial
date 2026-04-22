@extends('layouts.shop')

@section('title', 'Catalog')

@section('content')
    <div class="flex flex-col gap-6 lg:flex-row">
        <aside class="w-full rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm lg:max-w-xs">
            <h2 class="text-sm font-bold uppercase tracking-wide text-zinc-800">Filters</h2>
            <form method="GET" class="mt-4 space-y-4" id="catalog-filter-form">
                <div>
                    <label class="text-xs font-semibold text-zinc-600">Category (includes sub-products)</label>
                    <select name="category_id" class="mt-1 w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 shadow-sm">
                        <option value="">All categories</option>
                        @foreach ($categoryOptions as $opt)
                            <option value="{{ $opt['id'] }}" @selected(($filters['category_id'] ?? null) == $opt['id'])>{{ $opt['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-zinc-600">Search</label>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Product name…" class="mt-1 w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-zinc-600">Min ₹</label>
                        <input type="number" step="0.01" name="min_price" id="filter-min-price" value="{{ $filters['min_price'] ?? '' }}" class="mt-1 w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 shadow-sm">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-zinc-600">Max ₹</label>
                        <input type="number" step="0.01" name="max_price" id="filter-max-price" value="{{ $filters['max_price'] ?? '' }}" class="mt-1 w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 shadow-sm">
                    </div>
                </div>
                <button class="w-full rounded-xl bg-amber-600 px-3 py-2.5 text-sm font-bold text-zinc-900 hover:bg-amber-500">Apply</button>
            </form>
            <p class="mt-4 text-[11px] leading-relaxed text-zinc-500">Tip: open the ☰ menu to browse the full category tree (main → sub → sub-sub).</p>
        </aside>

        <div class="flex-1 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-bold text-zinc-900">Products</h1>
                <button type="button" @click="menuOpen = true" class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-800 hover:border-amber-400 hover:text-amber-800 lg:hidden">Categories</button>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($products as $product)
                    @php
                        $img = $product->images->first();
                        $defaultVariant = $product->variants->firstWhere('price', '>', 0) ?? $product->variants->first();
                    @endphp
                    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:border-amber-200 hover:shadow-md">
                        <a href="{{ route('shop.product', $product->slug) }}" class="block">
                            @if ($img)
                                <img src="{{ $img->url() }}" class="h-40 w-full object-cover" alt="{{ $product->name }}" width="400" height="160" loading="lazy">
                            @else
                                <div class="flex h-40 items-center justify-center bg-zinc-100 text-xs text-zinc-500">No image</div>
                            @endif
                        </a>
                        <div class="p-4">
                            <a href="{{ route('shop.product', $product->slug) }}" class="block">
                                <p class="text-base font-semibold text-zinc-900 hover:text-emerald-900">{{ $product->name }}</p>
                            </a>
                            <p class="line-clamp-2 text-sm text-zinc-700">{{ $product->short_description }}</p>
                            <p class="mt-2 text-sm font-bold text-amber-700">From ₹{{ number_format($product->fromPrice(), 2) }}</p>

                            @if ($defaultVariant)
                                <form method="POST" action="{{ route('shop.cart.add') }}" class="mt-3 flex flex-wrap gap-2">
                                    @csrf
                                    <input type="hidden" name="product_variant_id" value="{{ $defaultVariant->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="rounded-lg bg-brand-600 px-3 py-2 text-xs font-semibold text-zinc-950 hover:bg-brand-500">Add to cart</button>
                                    <button type="submit" name="buy_now" value="1" class="rounded-lg border border-zinc-300 px-3 py-2 text-xs font-semibold text-zinc-900 hover:bg-zinc-50">Buy now</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div>
                {{ $products->links() }}
            </div>
        </div>
    </div>

    <script>
        (() => {
            const form = document.getElementById('catalog-filter-form');
            const minInput = document.getElementById('filter-min-price');
            const maxInput = document.getElementById('filter-max-price');

            if (!form || !minInput || !maxInput) {
                return;
            }

            form.addEventListener('submit', () => {
                const minValue = minInput.value.trim();
                const maxValue = maxInput.value.trim();

                if (minValue === '' || maxValue === '') {
                    return;
                }

                const min = Number(minValue);
                const max = Number(maxValue);

                if (Number.isFinite(min) && Number.isFinite(max) && min > max) {
                    minInput.value = String(max);
                    maxInput.value = String(min);
                }
            });
        })();
    </script>
@endsection
