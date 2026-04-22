<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Account</p>
                <h2 class="text-xl font-bold leading-tight text-zinc-900">Dashboard</h2>
            </div>
            <a href="{{ route('shop.catalog') }}" class="inline-flex rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-800 hover:bg-zinc-50">Browse catalog</a>
        </div>
    </x-slot>

    @php
        $user = auth()->user();
        $orders = $user->orders()->latest('placed_at')->take(5)->get();
        $totalOrders = (int) $user->orders()->count();
        $activeOrders = (int) $user->orders()->whereIn('status', ['pending', 'processing', 'dispatched', 'in_transit', 'delayed'])->count();
        $deliveredOrders = (int) $user->orders()->where('status', 'delivered')->count();
        $cartCount = (int) $user->cartItems()->sum('quantity');
        $defaultAddress = $user->addresses()->where('is_default', true)->first() ?? $user->addresses()->latest('id')->first();
    @endphp

    <div class="bg-gradient-to-b from-zinc-100 to-zinc-50 py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Total orders</p>
                    <p class="mt-2 text-3xl font-bold text-zinc-900">{{ $totalOrders }}</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Active orders</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-700">{{ $activeOrders }}</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Delivered</p>
                    <p class="mt-2 text-3xl font-bold text-zinc-900">{{ $deliveredOrders }}</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Items in cart</p>
                    <p class="mt-2 text-3xl font-bold text-amber-700">{{ $cartCount }}</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm lg:col-span-2">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-lg font-semibold text-zinc-900">Recent orders</h3>
                        <a href="{{ route('account.orders.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">View all</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($orders as $order)
                            @php
                                $statusTone = match ($order->status->value) {
                                    'delivered' => 'bg-emerald-100 text-emerald-800',
                                    'cancelled' => 'bg-rose-100 text-rose-800',
                                    'delayed' => 'bg-amber-100 text-amber-800',
                                    default => 'bg-zinc-100 text-zinc-700',
                                };
                            @endphp
                            <a href="{{ route('account.orders.show', $order) }}" class="flex flex-col gap-3 rounded-xl border border-zinc-200 px-4 py-3 transition hover:border-emerald-300 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-zinc-900">{{ $order->reference }}</p>
                                    <p class="text-xs text-zinc-500">{{ $order->placed_at?->format('M d, Y h:i A') ?? '—' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-zinc-900">₹{{ number_format($order->grand_total, 2) }}</p>
                                    <span class="mt-1 inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase {{ $statusTone }}">{{ str_replace('_', ' ', $order->status->value) }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 px-4 py-8 text-center">
                                <p class="text-sm text-zinc-600">No orders yet. Start shopping to see your activity here.</p>
                                <a href="{{ route('shop.catalog') }}" class="mt-3 inline-flex rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Shop now</a>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-zinc-900">Quick actions</h3>
                        <div class="mt-4 grid gap-2">
                            <a href="{{ route('shop.catalog') }}" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 px-3 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50">Browse products</a>
                            <a href="{{ route('shop.cart') }}" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 px-3 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50">Open cart</a>
                            <a href="{{ route('account.orders.index') }}" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 px-3 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50">Track orders</a>
                            <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 px-3 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50">Edit profile</a>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-zinc-900">Default address</h3>
                        @if ($defaultAddress)
                            <p class="mt-3 text-sm text-zinc-700">{{ $defaultAddress->line1 }}</p>
                            @if ($defaultAddress->line2)
                                <p class="text-sm text-zinc-700">{{ $defaultAddress->line2 }}</p>
                            @endif
                            <p class="text-sm text-zinc-700">{{ $defaultAddress->city }}, {{ $defaultAddress->state }} {{ $defaultAddress->postal_code }}</p>
                            <p class="text-sm text-zinc-700">{{ $defaultAddress->country }}</p>
                        @else
                            <p class="mt-3 text-sm text-zinc-600">No saved address yet. Add one during checkout.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
