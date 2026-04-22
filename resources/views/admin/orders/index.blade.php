@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <p class="text-sm text-zinc-600">Track new orders, payment state, and fulfillment status.</p>
    </div>

    <form method="GET" class="mt-4 flex flex-wrap gap-3 text-sm">
        <select name="status" class="rounded-lg border-zinc-300 bg-white text-zinc-900 shadow-sm">
            <option value="">All statuses</option>
            @foreach (['pending','processing','dispatched','in_transit','delivered','cancelled','delayed'] as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst(str_replace('_',' ', $status)) }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="rounded-lg border-zinc-300 bg-white text-zinc-900 shadow-sm">
        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="rounded-lg border-zinc-300 bg-white text-zinc-900 shadow-sm">
        <button class="rounded-lg bg-zinc-900 px-4 py-2 font-semibold text-white hover:bg-zinc-800">Filter</button>
    </form>

    <div class="mt-6 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-zinc-200 text-sm">
            <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-600">
                <tr>
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Items</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Payment</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @foreach ($orders as $order)
                    @php
                        $first = $order->items->first();
                        $thumb = $first?->variant?->product?->images?->first();
                    @endphp
                    <tr>
                        <td class="px-4 py-3 font-semibold text-zinc-900">{{ $order->reference }}</td>
                        <td class="px-4 py-3 text-zinc-700">{{ $order->user->name }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @if ($thumb)
                                    <img src="{{ $thumb->url() }}" alt="" class="h-9 w-9 rounded-md object-cover ring-1 ring-zinc-200" width="36" height="36">
                                @else
                                    <span class="flex h-9 w-9 items-center justify-center rounded-md bg-zinc-100 text-[10px] font-medium text-zinc-500">No</span>
                                @endif
                                <span class="text-zinc-700">{{ $order->items->sum('quantity') }} pcs</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-semibold text-zinc-900">₹{{ number_format($order->grand_total, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold bg-zinc-100 text-zinc-700">{{ str_replace('_', ' ', $order->status->value) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $order->payment_status->value === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $order->payment_status->value }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-emerald-700 hover:text-emerald-800">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-zinc-100 px-4 py-3">{{ $orders->links() }}</div>
    </div>
@endsection
