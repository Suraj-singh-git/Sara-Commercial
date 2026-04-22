@extends('layouts.admin')

@section('title', $user->name)

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">{{ $user->name }}</h1>
    <p class="text-sm text-slate-600">{{ $user->email }} · {{ $user->phone ?? 'No phone' }}</p>

    <div class="mt-8 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Order history</h2>
        <ul class="mt-3 space-y-2 text-sm text-slate-700">
            @forelse ($user->orders as $order)
                <li class="flex justify-between border-b border-slate-100 pb-2">
                    <span>{{ $order->reference }} — {{ $order->status->value }}</span>
                    <span class="font-semibold">₹{{ number_format($order->grand_total, 2) }}</span>
                </li>
            @empty
                <li>No orders yet.</li>
            @endforelse
        </ul>
    </div>
@endsection
