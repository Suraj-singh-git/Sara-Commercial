<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $websiteSettings->site_title ?? config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900" x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false">
    {{-- Utility strip (Toolsvilla-style) --}}
    <div class="hidden border-b text-xs text-emerald-100/95 sm:block" style="border-color: {{ $websiteSettings->theme_color ?? '#065f46' }}; background-color: {{ $websiteSettings->theme_color ?? '#065f46' }}">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2">
            <span class="inline-flex items-center gap-2">
                <svg class="h-3.5 w-3.5 shrink-0 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>support@saracommercial.local</span>
            </span>
            <span class="flex items-center gap-4 font-medium">
                <a href="{{ auth()->check() ? route('account.orders.index') : route('login') }}" class="text-white hover:text-amber-200">Track order</a>
                <span class="text-emerald-700/80">|</span>
                <a href="{{ route('shop.catalog') }}" class="text-white hover:text-amber-200">Catalog</a>
            </span>
        </div>
    </div>
    <header class="sticky top-0 z-40 border-b text-white shadow-lg" style="border-color: {{ $websiteSettings->theme_color ?? '#065f46' }}; background-color: {{ $websiteSettings->theme_color ?? '#065f46' }}">
        <div class="h-1 bg-gradient-to-r from-amber-400 via-amber-500 to-amber-400"></div>
        <div class="mx-auto flex max-w-7xl items-center gap-3 px-3 py-3 sm:px-4">
            <button type="button"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-white/15 bg-emerald-900/50 text-white hover:bg-emerald-900"
                    @click="menuOpen = true"
                    aria-label="Open categories menu">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <a href="{{ route('shop.home') }}" class="flex min-w-0 items-center gap-2 sm:flex-initial">
                @if (($websiteSettings->logoUrl() ?? null))
                    <img src="{{ $websiteSettings->logoUrl() }}" alt="" class="h-8 w-8 rounded-md object-cover ring-1 ring-white/20">
                @endif
                <span class="min-w-0">
                    <span class="block truncate text-lg font-bold tracking-tight text-white sm:text-xl">{{ $websiteSettings->company_name ?? 'Sara Commercial' }}</span>
                    <span class="block truncate text-[10px] font-medium uppercase tracking-widest text-amber-300 sm:text-xs">Industrial supplies & equipment</span>
                </span>
            </a>

            <div class="hidden max-w-md flex-1 px-4 md:block">
                <form action="{{ route('shop.catalog') }}" method="GET" class="relative">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search machinery, tools, parts…"
                           class="w-full rounded-lg border border-white/15 bg-emerald-900/60 py-2 pl-3 pr-10 text-sm text-white placeholder:text-emerald-200/70 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/40">
                    <button type="submit" class="absolute right-1 top-1 rounded-md bg-amber-500 px-3 py-1 text-xs font-bold text-emerald-950 hover:bg-amber-400">Go</button>
                </form>
            </div>

            <nav class="flex shrink-0 items-center gap-1 sm:gap-2">
                <a href="{{ route('shop.cart') }}" class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-white/15 bg-emerald-900/50 hover:bg-emerald-900" title="Cart">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    @if (($cartCount ?? 0) > 0)
                        <span class="absolute -right-1.5 -top-1.5 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-amber-400 px-1 text-[10px] font-bold leading-none text-emerald-950">
                            {{ $cartCount > 99 ? '99+' : $cartCount }}
                        </span>
                    @endif
                </a>
                @auth
                    <a href="{{ route('shop.checkout') }}" class="hidden rounded-lg border border-white/15 px-2 py-2 text-xs font-semibold hover:bg-emerald-900 sm:inline">Checkout</a>
                    <a href="{{ route('account.orders.index') }}" class="hidden rounded-lg border border-white/15 px-2 py-2 text-xs font-semibold hover:bg-emerald-900 lg:inline">Orders</a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-lg bg-amber-400 px-2 py-2 text-xs font-bold text-emerald-950 hover:bg-amber-300">Admin</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="rounded-lg border border-white/15 px-2 py-2 text-xs font-semibold hover:bg-emerald-900">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg border border-white/15 px-2 py-2 text-xs font-semibold hover:bg-emerald-900">Login</a>
                    <a href="{{ route('otp.login') }}" class="hidden rounded-lg border border-white/15 px-2 py-2 text-xs font-semibold hover:bg-emerald-900 sm:inline">OTP</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-amber-400 px-2 py-2 text-xs font-bold text-emerald-950 hover:bg-amber-300">Signup</a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- Side drawer: categories → sub → sub-sub (from admin) --}}
    <div x-show="menuOpen" x-transition.opacity class="fixed inset-0 z-50 bg-black/60" @click="menuOpen = false" style="display: none;"></div>
    <aside x-show="menuOpen"
           x-transition:enter="transition transform duration-200 ease-out"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition transform duration-150 ease-in"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="fixed inset-y-0 left-0 z-50 flex w-[min(100vw,20rem)] flex-col border-r border-emerald-900 bg-emerald-950 text-white shadow-2xl"
           style="display: none;">
        <div class="flex items-center justify-between border-b border-white/10 px-4 py-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-400">Shop by category</p>
                <p class="text-sm font-bold text-white">{{ $websiteSettings->company_name ?? 'Sara Commercial' }}</p>
            </div>
            <button type="button" class="rounded-lg p-2 hover:bg-white/10" @click="menuOpen = false" aria-label="Close menu">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-3 py-4">
            <a href="{{ route('shop.catalog') }}" class="mb-3 block rounded-lg bg-amber-600 px-3 py-2 text-center text-sm font-bold text-zinc-900 hover:bg-amber-500" @click="menuOpen = false">
                Full catalog
            </a>
            @if (($menuCategories ?? collect())->isEmpty())
                <p class="text-sm text-white/60">Categories will appear here once added in Admin.</p>
            @else
                <x-shop.nav-category-branch :nodes="$menuCategories" />
            @endif
        </div>
    </aside>

    <main class="mx-auto max-w-7xl px-3 py-8 sm:px-4">
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-zinc-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-8">
            <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                <div class="text-center sm:text-left">
                    <p class="font-bold text-zinc-900">{{ $websiteSettings->company_name ?? 'Sara Commercial' }}</p>
                    <p class="text-xs text-zinc-500">Quality tools & industrial equipment — curated catalog, dependable fulfilment.</p>
                </div>
                <p class="text-xs text-zinc-400">© {{ date('Y') }} {{ $websiteSettings->company_name ?? 'Sara Commercial' }}</p>
            </div>
        </div>
    </footer>
</body>
</html>
