<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') · {{ $websiteSettings->company_name ?? 'Sara Commercial' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-zinc-100 font-sans text-zinc-900 antialiased" x-data="{ sidebarOpen: false }" @keydown.window.escape="sidebarOpen = false">
    {{-- Mobile overlay --}}
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        x-cloak
        class="fixed inset-0 z-40 bg-zinc-900/60 backdrop-blur-sm lg:hidden"
        @click="sidebarOpen = false"
        aria-hidden="true"
    ></div>

    <div class="flex min-h-full">
        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-zinc-800/80 bg-zinc-950 text-zinc-300 shadow-2xl transition-transform duration-200 ease-out lg:static lg:z-0 lg:translate-x-0 lg:shadow-none"
            :class="sidebarOpen ? 'max-lg:!translate-x-0' : ''"
        >
            <div class="flex h-16 shrink-0 items-center justify-between border-b border-zinc-800/80 px-5 lg:h-[4.25rem]">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    @if (($websiteSettings->logoUrl() ?? null))
                        <img src="{{ $websiteSettings->logoUrl() }}" alt="" class="h-9 w-9 rounded-lg object-cover">
                    @else
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg text-sm font-black text-white" style="background-color: {{ $websiteSettings->theme_color ?? '#065f46' }}">S</span>
                    @endif
                    <div>
                        <p class="text-sm font-bold tracking-tight text-white">{{ $websiteSettings->company_name ?? 'Sara Commercial' }}</p>
                        <p class="text-[11px] font-medium uppercase tracking-wider text-zinc-500">Admin</p>
                    </div>
                </a>
                <button type="button" class="rounded-lg p-2 text-zinc-400 hover:bg-zinc-800 hover:text-white lg:hidden" @click="sidebarOpen = false" aria-label="Close menu">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-4 text-sm font-medium">
                <a class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.dashboard') ? 'bg-zinc-800 text-white shadow-inner' : 'text-zinc-400 hover:bg-zinc-800/60 hover:text-white' }}" href="{{ route('admin.dashboard') }}">
                    <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Dashboard
                </a>
                <a class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.categories.*') ? 'bg-zinc-800 text-white shadow-inner' : 'text-zinc-400 hover:bg-zinc-800/60 hover:text-white' }}" href="{{ route('admin.categories.index') }}">
                    <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Categories
                </a>
                <a class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.products.*') ? 'bg-zinc-800 text-white shadow-inner' : 'text-zinc-400 hover:bg-zinc-800/60 hover:text-white' }}" href="{{ route('admin.products.index') }}">
                    <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Products
                </a>
                <a class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.orders.*') ? 'bg-zinc-800 text-white shadow-inner' : 'text-zinc-400 hover:bg-zinc-800/60 hover:text-white' }}" href="{{ route('admin.orders.index') }}">
                    <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    Orders
                </a>
                <a class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.users.*') ? 'bg-zinc-800 text-white shadow-inner' : 'text-zinc-400 hover:bg-zinc-800/60 hover:text-white' }}" href="{{ route('admin.users.index') }}">
                    <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Users
                </a>
                <a class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.settings.*') ? 'bg-zinc-800 text-white shadow-inner' : 'text-zinc-400 hover:bg-zinc-800/60 hover:text-white' }}" href="{{ route('admin.settings.edit') }}">
                    <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 4a7.94 7.94 0 00-.12-1l2.01-1.57-2-3.46-2.43.98a7.95 7.95 0 00-1.73-1l-.37-2.58h-4l-.37 2.58a7.95 7.95 0 00-1.73 1l-2.43-.98-2 3.46 2.01 1.57a7.94 7.94 0 000 2l-2.01 1.57 2 3.46 2.43-.98c.53.41 1.11.75 1.73 1l.37 2.58h4l.37-2.58c.62-.25 1.2-.59 1.73-1l2.43.98 2-3.46L20.82 13c.08-.33.12-.66.12-1z"/></svg>
                    Website settings
                </a>
            </nav>

            <div class="border-t border-zinc-800/80 p-4">
                <a href="{{ route('shop.home') }}" class="flex items-center justify-center gap-2 rounded-xl border border-zinc-700/80 bg-zinc-900/50 px-3 py-2.5 text-xs font-semibold text-white transition hover:border-amber-500/50 hover:bg-zinc-900">
                    <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    View storefront
                </a>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full rounded-xl px-3 py-2 text-left text-xs font-medium text-zinc-500 transition hover:bg-zinc-800/50 hover:text-zinc-300">Sign out</button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col lg:pl-0">
            <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-4 border-b border-zinc-200/80 bg-white/90 px-4 backdrop-blur-md sm:px-6 lg:px-8">
                <button type="button" class="inline-flex items-center justify-center rounded-lg border border-zinc-200 bg-white p-2 text-zinc-700 shadow-sm hover:bg-zinc-50 lg:hidden" @click="sidebarOpen = true" aria-label="Open menu">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="min-w-0 flex-1">
                    <h1 class="truncate text-base font-semibold text-zinc-900 sm:text-lg">@yield('title', 'Admin')</h1>
                </div>
                <a href="{{ route('shop.home') }}" class="hidden text-sm font-semibold text-amber-700 hover:text-amber-800 sm:inline">Storefront</a>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 sm:py-8 lg:px-10">
                @if (session('status'))
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 shadow-sm">{{ session('status') }}</div>
                @endif
                <div class="mx-auto max-w-7xl">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>
</html>
