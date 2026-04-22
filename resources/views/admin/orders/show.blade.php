@extends('layouts.admin')

@section('title', $order->reference)

@section('content')
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase text-zinc-500">Order</p>
            <h1 class="text-2xl font-bold text-zinc-900">{{ $order->reference }}</h1>
            <p class="text-sm text-zinc-600">{{ $order->user->name }} · {{ $order->user->email }}</p>
        </div>
        <div class="text-sm text-zinc-700">
            <p>Status: <span class="rounded-full bg-zinc-100 px-2 py-0.5 font-semibold">{{ str_replace('_', ' ', $order->status->value) }}</span></p>
            <p class="mt-1">Payment: <span class="rounded-full px-2 py-0.5 font-semibold {{ $order->payment_status->value === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $order->payment_status->value }}</span> ({{ str_replace('_', ' ', $order->payment_mode->value) }})</p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-zinc-900">Items</h2>
            <ul class="mt-3 divide-y divide-zinc-100">
                @foreach ($order->items as $item)
                    @php $thumb = $item->variant?->product?->images?->first(); @endphp
                    <li class="flex gap-3 py-3 first:pt-0">
                        <div class="shrink-0">
                            @if ($thumb)
                                <img src="{{ $thumb->url() }}" alt="" class="h-14 w-14 rounded-md object-cover ring-1 ring-zinc-200" width="56" height="56">
                            @else
                                <span class="flex h-14 w-14 items-center justify-center rounded-md bg-zinc-100 text-[10px] font-medium text-zinc-500">No</span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-zinc-900">{{ $item->product_name }}</p>
                            <p class="text-xs text-zinc-600">{{ $item->variant_label }} × {{ $item->quantity }}</p>
                        </div>
                        <span class="text-sm font-semibold text-zinc-900">₹{{ number_format($item->line_total, 2) }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-4 text-right text-base font-bold text-zinc-900">Total ₹{{ number_format($order->grand_total, 2) }}</p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-zinc-900">Shipping</h2>
            @php $addr = $order->shippingAddress; @endphp
            @if ($addr)
                <p class="mt-2 text-sm text-zinc-700">{{ $addr->contact_name }} · {{ $addr->phone }}</p>
                <p class="text-sm text-zinc-700">{{ $addr->line1 }} {{ $addr->line2 }}</p>
                <p class="text-sm text-zinc-700">{{ $addr->city }}, {{ $addr->state }} {{ $addr->postal_code }}</p>
            @endif
            <p class="mt-3 text-sm text-zinc-600">Waybill: {{ $order->delhivery_waybill ?? 'pending' }}</p>
        </div>
    </div>

    <div class="mt-8 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-zinc-900">Status controls</h2>
        <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-3 flex flex-wrap items-end gap-3 text-sm">
            @csrf
            <div>
                <label class="text-xs font-semibold text-zinc-600">New status</label>
                <select name="status" class="mt-1 rounded-lg border-zinc-300">
                    @foreach (['pending','processing','dispatched','in_transit','delivered','cancelled'] as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <button class="rounded-lg bg-brand-600 px-3 py-2 font-semibold text-zinc-950 hover:bg-brand-500">Update</button>
        </form>

        <form method="POST" action="{{ route('admin.orders.delay', $order) }}" class="mt-6 space-y-3 text-sm">
            @csrf
            <h3 class="font-semibold text-zinc-900">Mark delayed</h3>
            <textarea name="delay_reason" class="w-full rounded-lg border-zinc-300" rows="3" placeholder="Reason" required></textarea>
            <input type="number" name="delay_days" min="1" class="w-32 rounded-lg border-zinc-300" placeholder="Days" required>
            <button class="rounded-lg bg-amber-500 px-3 py-2 font-semibold text-zinc-900 hover:bg-amber-400">Save delay</button>
        </form>
    </div>

    <div class="mt-8 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-zinc-900">Timeline</h2>
        <ol class="mt-3 space-y-2 text-sm text-zinc-700">
            @foreach ($order->statusEvents as $event)
                <li>{{ $event->recorded_at?->format('Y-m-d H:i') }} — {{ $event->status->value }}</li>
            @endforeach
        </ol>
    </div>
@endsection
