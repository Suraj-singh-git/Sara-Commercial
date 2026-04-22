<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Account</p>
                <h2 class="text-xl font-bold leading-tight text-zinc-900">My orders</h2>
            </div>
            <a href="{{ route('shop.catalog') }}" class="inline-flex rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-800 hover:bg-zinc-50">Continue shopping</a>
        </div>
    </x-slot>

    <div class="bg-gradient-to-b from-zinc-100 to-zinc-50 py-10">
        <div class="mx-auto max-w-5xl space-y-4 sm:px-6 lg:px-8">
            @forelse ($orders as $order)
                @php
                    $first = $order->items->first();
                    $thumb = $first?->variant?->product?->images?->first();
                    $statusTone = match ($order->status->value) {
                        'delivered' => 'bg-emerald-100 text-emerald-800',
                        'cancelled' => 'bg-rose-100 text-rose-800',
                        'delayed' => 'bg-amber-100 text-amber-800',
                        default => 'bg-zinc-100 text-zinc-700',
                    };
                @endphp
                <a href="{{ route('account.orders.show', $order) }}" class="block rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-3">
                            @if ($thumb)
                                <img src="{{ $thumb->url() }}" alt="" class="h-14 w-14 rounded-lg object-cover ring-1 ring-zinc-200" width="56" height="56">
                            @else
                                <span class="flex h-14 w-14 items-center justify-center rounded-lg bg-zinc-100 text-[11px] font-medium text-zinc-500">No</span>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-zinc-900">{{ $order->reference }}</p>
                                <p class="text-xs text-zinc-500">{{ $order->placed_at?->format('M d, Y H:i') }}</p>
                                <p class="mt-1 text-xs text-zinc-600">{{ $order->items->sum('quantity') }} item(s)</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-zinc-900">₹{{ number_format($order->grand_total, 2) }}</p>
                            <p class="mt-1 inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase {{ $statusTone }}">{{ str_replace('_', ' ', $order->status->value) }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-5 py-10 text-center">
                    <p class="text-sm text-zinc-600">You have not placed any orders yet.</p>
                    <a href="{{ route('shop.catalog') }}" class="mt-3 inline-flex rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Browse products</a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
