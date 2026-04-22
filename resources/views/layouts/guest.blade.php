<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $websiteSettings->site_title ?? config('app.name', 'Laravel') }} · Account</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-zinc-900">
        <div class="min-h-screen bg-gradient-to-b from-zinc-100 via-zinc-50 to-white">
            <div class="hidden border-b text-xs text-emerald-100/95 sm:block" style="border-color: {{ $websiteSettings->theme_color ?? '#065f46' }}; background-color: {{ $websiteSettings->theme_color ?? '#065f46' }}">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2">
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-3.5 w-3.5 shrink-0 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>support@saracommercial.local</span>
                    </span>
                    <span class="flex items-center gap-4 font-medium">
                        <a href="{{ route('shop.catalog') }}" class="text-white hover:text-amber-200">Catalog</a>
                        <span class="text-emerald-700/80">|</span>
                        <a href="{{ route('login') }}" class="text-white hover:text-amber-200">Login</a>
                    </span>
                </div>
            </div>

            <header class="sticky top-0 z-40 border-b text-white shadow-lg" style="border-color: {{ $websiteSettings->theme_color ?? '#065f46' }}; background-color: {{ $websiteSettings->theme_color ?? '#065f46' }}">
                <div class="h-1 bg-gradient-to-r from-amber-400 via-amber-500 to-amber-400"></div>
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-3 py-3 sm:px-4">
                    <a href="{{ route('shop.home') }}" class="flex min-w-0 items-center gap-2 sm:flex-initial">
                        @if (($websiteSettings->logoUrl() ?? null))
                            <img src="{{ $websiteSettings->logoUrl() }}" alt="" class="h-8 w-8 rounded-md object-cover ring-1 ring-white/20">
                        @endif
                        <span class="min-w-0">
                            <span class="block truncate text-lg font-bold tracking-tight text-white sm:text-xl">{{ $websiteSettings->company_name ?? 'Sara Commercial' }}</span>
                            <span class="block truncate text-[10px] font-medium uppercase tracking-widest text-amber-300 sm:text-xs">Industrial supplies & equipment</span>
                        </span>
                    </a>

                    <nav class="flex shrink-0 items-center gap-1 sm:gap-2">
                        <a href="{{ route('shop.catalog') }}" class="hidden rounded-lg border border-white/15 px-2 py-2 text-xs font-semibold hover:bg-emerald-900 sm:inline">Catalog</a>
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
                            <a href="{{ route('register') }}" class="rounded-lg bg-amber-400 px-2 py-2 text-xs font-bold text-emerald-950 hover:bg-amber-300">Signup</a>
                        @endauth
                    </nav>
                </div>
            </header>

            <main class="px-4 py-10 sm:px-6 lg:px-8">
                <div class="mx-auto w-full max-w-md">
                    <p class="mb-4 text-center text-xs font-medium uppercase tracking-[0.2em] text-zinc-500">Account access</p>
                    <div class="rounded-2xl border border-zinc-200/80 bg-white p-8 shadow-xl shadow-zinc-900/[0.06] ring-1 ring-zinc-900/[0.04] sm:p-10">
                        {{ $slot }}
                    </div>

                    <p class="mt-6 text-center text-xs text-zinc-500">
                        © {{ date('Y') }} {{ $websiteSettings->company_name ?? 'Sara Commercial' }} · Need help? Contact support from your order confirmation email.
                    </p>
                </div>
            </main>
        </div>
    </body>
</html>
