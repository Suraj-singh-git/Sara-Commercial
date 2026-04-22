@extends('layouts.shop')

@section('title', 'Sara Commercial — Industrial Store')

@section('content')
    {{-- Solid emerald base + gradient so text stays readable even if gradient utilities fail to load --}}
    <section class="overflow-hidden rounded-2xl border border-emerald-900/40 bg-emerald-950 bg-gradient-to-br from-emerald-950 via-emerald-900 to-emerald-950 text-white shadow-xl">
        <div class="grid gap-8 p-8 lg:grid-cols-2 lg:items-center lg:p-12">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-300">India’s trusted partner</p>
                <h1 class="mt-3 text-3xl font-extrabold leading-tight text-white sm:text-4xl">Machinery, tools & equipment for your workshop</h1>
                <p class="mt-4 max-w-xl text-sm leading-relaxed text-emerald-100/95">Browse by category from the side menu — same flow as leading industrial marketplaces. Every category level is managed from your admin panel with real photos.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="button" @click="menuOpen = true" class="inline-flex items-center gap-2 rounded-xl bg-amber-400 px-5 py-3 text-sm font-bold text-emerald-950 shadow hover:bg-amber-300">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        Open categories
                    </button>
                    <a href="{{ route('shop.catalog') }}" class="inline-flex items-center rounded-xl border-2 border-amber-300/50 bg-emerald-950/30 px-5 py-3 text-sm font-semibold text-white backdrop-blur-sm hover:border-amber-300 hover:bg-emerald-900/50">Browse all products</a>
                </div>
            </div>
            <div class="relative hidden lg:block">
                <div class="aspect-[4/3] overflow-hidden rounded-2xl border-2 border-amber-400/30 shadow-2xl ring-1 ring-white/10">
                    <img src="https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?auto=format&fit=crop&w=900&q=80" alt="Industrial workshop" class="h-full w-full object-cover">
                </div>
            </div>
        </div>
    </section>

    @if (($menuCategories ?? collect())->isNotEmpty())
        <section class="mt-12">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-zinc-900">Shop by category</h2>
                    <p class="mt-1 text-sm text-zinc-700">Top-level categories — open the side menu for full sub & sub-sub navigation.</p>
                </div>
                <button type="button" @click="menuOpen = true" class="text-sm font-semibold text-emerald-800 hover:text-emerald-900">Menu →</button>
            </div>
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($menuCategories as $cat)
                    <a href="{{ route('shop.catalog', ['category_id' => $cat->id]) }}" class="group flex overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md">
                        <div class="relative h-28 w-28 shrink-0 bg-zinc-100">
                            @if ($cat->menu_image_path)
                                <img src="{{ $cat->menuImageUrl() }}" alt="{{ $cat->name }}" class="h-full w-full object-cover transition group-hover:scale-105" width="112" height="112">
                            @else
                                <img src="https://images.unsplash.com/photo-1581092160562-40aa08e66837?auto=format&fit=crop&w=300&q=80" alt="" class="h-full w-full object-cover opacity-80">
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col justify-center p-4">
                            <p class="font-bold text-zinc-900 group-hover:text-emerald-900">{{ $cat->name }}</p>
                            <p class="mt-1 text-xs text-zinc-600">{{ $cat->children->count() }} subcategories</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mt-14">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-zinc-900">Featured products</h2>
            <a href="{{ route('shop.catalog') }}" class="text-sm font-semibold text-emerald-800 hover:text-emerald-900">View catalog</a>
        </div>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($featured as $product)
                <a href="{{ route('shop.product', $product->slug) }}" class="group overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm hover:border-emerald-200">
                    @php $img = $product->images->first(); @endphp
                    <div class="aspect-[4/3] bg-zinc-100">
                        @if ($img)
                            <img src="{{ $img->url() }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition group-hover:scale-105" width="400" height="300" loading="lazy">
                        @else
                            <div class="flex h-full items-center justify-center text-xs font-medium text-zinc-500">No image</div>
                        @endif
                    </div>
                    <div class="p-4">
                        <p class="line-clamp-2 text-sm font-semibold text-zinc-900 group-hover:text-emerald-900">{{ $product->name }}</p>
                        <p class="mt-2 text-sm font-bold text-amber-700">From ₹{{ number_format($product->fromPrice(), 2) }}</p>
                    </div>
                </a>
            @empty
                <p class="col-span-full text-sm text-zinc-700">No products yet. Run the seeder or add items in Admin.</p>
            @endforelse
        </div>
    </section>
@endsection
