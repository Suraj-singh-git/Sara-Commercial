<nav x-data="{ open: false }" class="border-b text-white shadow-lg" style="border-color: {{ $websiteSettings->theme_color ?? '#065f46' }}; background-color: {{ $websiteSettings->theme_color ?? '#065f46' }}">
    <div class="hidden border-b text-xs text-emerald-100/95 sm:block" style="border-color: {{ $websiteSettings->theme_color ?? '#065f46' }}; background-color: {{ $websiteSettings->theme_color ?? '#065f46' }}">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2">
            <span class="inline-flex items-center gap-2">
                <svg class="h-3.5 w-3.5 shrink-0 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>support@saracommercial.local</span>
            </span>
            <a href="{{ route('shop.catalog') }}" class="font-medium text-white hover:text-amber-200">Catalog</a>
        </div>
    </div>

    <div class="h-1 bg-gradient-to-r from-amber-400 via-amber-500 to-amber-400"></div>

    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-3 sm:px-4">
        <div class="flex min-w-0 items-center gap-3">
            <a href="{{ route('shop.home') }}" class="flex min-w-0 items-center gap-2">
                @if (($websiteSettings->logoUrl() ?? null))
                    <img src="{{ $websiteSettings->logoUrl() }}" alt="" class="h-8 w-8 rounded-md object-cover ring-1 ring-white/20">
                @endif
                <span class="min-w-0">
                    <span class="block truncate text-sm font-bold tracking-tight text-white sm:text-base">{{ $websiteSettings->company_name ?? 'Sara Commercial' }}</span>
                <span class="block truncate text-[10px] font-medium uppercase tracking-widest text-amber-300">Industrial supplies & equipment</span>
                </span>
            </a>

            <div class="hidden items-center gap-2 md:flex">
                <a href="{{ route('shop.home') }}" class="rounded-lg border border-white/15 px-2 py-2 text-xs font-semibold {{ request()->routeIs('shop.*') ? 'bg-emerald-900 text-white' : 'text-emerald-100 hover:bg-emerald-900' }}">Store</a>
                <a href="{{ route('dashboard') }}" class="rounded-lg border border-white/15 px-2 py-2 text-xs font-semibold {{ request()->routeIs('dashboard') ? 'bg-emerald-900 text-white' : 'text-emerald-100 hover:bg-emerald-900' }}">Dashboard</a>
                <a href="{{ route('shop.checkout') }}" class="rounded-lg border border-white/15 px-2 py-2 text-xs font-semibold text-emerald-100 hover:bg-emerald-900">Checkout</a>
                <a href="{{ route('account.orders.index') }}" class="rounded-lg border border-white/15 px-2 py-2 text-xs font-semibold {{ request()->routeIs('account.orders.*') ? 'bg-emerald-900 text-white' : 'text-emerald-100 hover:bg-emerald-900' }}">Track order</a>
                @if (Auth::user()?->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg bg-amber-400 px-3 py-2 text-xs font-bold text-emerald-950 hover:bg-amber-300">Admin</a>
                @endif
            </div>
        </div>

        <div class="hidden sm:flex sm:items-center sm:gap-2">
            <x-dropdown align="right" width="56">
                <x-slot name="trigger">
                    <button class="inline-flex items-center gap-2 rounded-lg border border-white/15 bg-emerald-900/50 px-2 py-2 text-xs font-semibold text-white hover:bg-emerald-900 focus:outline-none">
                        <span>{{ Auth::user()->name }}</span>
                        <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                    <x-dropdown-link :href="route('account.orders.index')">My orders</x-dropdown-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                            Log Out
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>

        <div class="sm:hidden">
            <button @click="open = !open" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-white/15 bg-emerald-900/50 text-white hover:bg-emerald-900">
                <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{'hidden': open, 'inline-flex': ! open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'hidden': ! open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-white/10 bg-emerald-950 sm:hidden">
        <div class="space-y-1 px-3 py-3">
            <a href="{{ route('shop.home') }}" class="block rounded-lg border border-white/15 px-3 py-2 text-sm font-semibold text-emerald-100 hover:bg-emerald-900">Store</a>
            <a href="{{ route('dashboard') }}" class="block rounded-lg border border-white/15 px-3 py-2 text-sm font-semibold text-emerald-100 hover:bg-emerald-900">Dashboard</a>
            <a href="{{ route('shop.checkout') }}" class="block rounded-lg border border-white/15 px-3 py-2 text-sm font-semibold text-emerald-100 hover:bg-emerald-900">Checkout</a>
            <a href="{{ route('account.orders.index') }}" class="block rounded-lg border border-white/15 px-3 py-2 text-sm font-semibold text-emerald-100 hover:bg-emerald-900">Track order</a>
            @if (Auth::user()?->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="block rounded-lg border border-white/15 px-3 py-2 text-sm font-semibold text-emerald-100 hover:bg-emerald-900">Admin</a>
            @endif
            <a href="{{ route('profile.edit') }}" class="block rounded-lg border border-white/15 px-3 py-2 text-sm font-semibold text-emerald-100 hover:bg-emerald-900">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-lg border border-white/15 px-3 py-2 text-left text-sm font-semibold text-emerald-100 hover:bg-emerald-900">Log Out</button>
            </form>
        </div>
    </div>
</nav>
