@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-zinc-600">Orders, paid revenue, and gross value for the selected window.</p>
        </div>
        <form method="GET" class="flex items-center gap-2 text-sm">
            <label class="font-semibold text-zinc-700">Range</label>
            <select name="range" class="rounded-md border-zinc-200" onchange="this.form.submit()">
                <option value="daily" @selected($range === 'daily')>Daily</option>
                <option value="weekly" @selected($range === 'weekly')>Weekly</option>
                <option value="monthly" @selected($range === 'monthly')>Monthly</option>
            </select>
        </form>
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-zinc-500">Paid revenue</p>
            <p class="mt-2 text-3xl font-bold text-zinc-900">₹{{ number_format($summary['revenue'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-zinc-500">Gross order value</p>
            <p class="mt-2 text-3xl font-bold text-zinc-900">₹{{ number_format($summary['gross'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-zinc-500">Orders</p>
            <p class="mt-2 text-3xl font-bold text-zinc-900">{{ $summary['orders'] }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-zinc-500">Active users</p>
            <p class="mt-2 text-3xl font-bold text-zinc-900">{{ $summary['active_users'] }}</p>
        </div>
    </div>

    @php
        $pointCount = max(1, count($series));
        $chartWidth = 640;
        $chartHeight = 240;
        $padding = 28;
        $innerWidth = $chartWidth - ($padding * 2);
        $innerHeight = $chartHeight - ($padding * 2);

        $maxGross = max(1.0, (float) collect($series)->max('gross'));
        $maxOrders = max(1, (int) collect($series)->max('orders'));

        $grossPoints = [];
        $paidPoints = [];
        $bars = [];

        foreach ($series as $i => $point) {
            $x = $padding + ($pointCount === 1 ? 0 : (int) round(($i / ($pointCount - 1)) * $innerWidth));
            $grossY = $padding + (int) round($innerHeight - (($point['gross'] / $maxGross) * $innerHeight));
            $paidY = $padding + (int) round($innerHeight - (($point['revenue'] / $maxGross) * $innerHeight));
            $barHeight = (int) round(($point['orders'] / $maxOrders) * $innerHeight);

            $grossPoints[] = $x.','.$grossY;
            $paidPoints[] = $x.','.$paidY;
            $bars[] = [
                'x' => $x - 16,
                'y' => $padding + $innerHeight - $barHeight,
                'h' => max(2, $barHeight),
                'label' => $point['label'],
                'orders' => $point['orders'],
            ];
        }
    @endphp

    <div class="mt-10 grid gap-6 xl:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-zinc-900">Revenue chart</h2>
            <p class="mt-1 text-xs text-zinc-500">Paid vs gross trend (last selected periods)</p>
            <div class="mt-4 overflow-x-auto">
                <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" class="h-64 min-w-[620px] w-full rounded-lg bg-zinc-50">
                    <line x1="{{ $padding }}" y1="{{ $padding }}" x2="{{ $padding }}" y2="{{ $padding + $innerHeight }}" stroke="#d4d4d8" stroke-width="1" />
                    <line x1="{{ $padding }}" y1="{{ $padding + $innerHeight }}" x2="{{ $padding + $innerWidth }}" y2="{{ $padding + $innerHeight }}" stroke="#d4d4d8" stroke-width="1" />
                    <polyline fill="none" stroke="#f59e0b" stroke-width="3" points="{{ implode(' ', $grossPoints) }}" />
                    <polyline fill="none" stroke="#10b981" stroke-width="3" points="{{ implode(' ', $paidPoints) }}" />
                    @foreach ($series as $i => $point)
                        @php
                            $x = $padding + ($pointCount === 1 ? 0 : (int) round(($i / ($pointCount - 1)) * $innerWidth));
                        @endphp
                        <text x="{{ $x }}" y="{{ $chartHeight - 6 }}" text-anchor="middle" font-size="10" fill="#71717a">{{ $point['label'] }}</text>
                    @endforeach
                </svg>
            </div>
            <div class="mt-4 flex items-center gap-4 text-xs text-zinc-600">
                <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-full bg-amber-300"></span>Gross</span>
                <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Paid</span>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-zinc-900">Orders chart</h2>
            <p class="mt-1 text-xs text-zinc-500">Orders volume by period</p>
            <div class="mt-4 overflow-x-auto">
                <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" class="h-64 min-w-[620px] w-full rounded-lg bg-zinc-50">
                    <line x1="{{ $padding }}" y1="{{ $padding }}" x2="{{ $padding }}" y2="{{ $padding + $innerHeight }}" stroke="#d4d4d8" stroke-width="1" />
                    <line x1="{{ $padding }}" y1="{{ $padding + $innerHeight }}" x2="{{ $padding + $innerWidth }}" y2="{{ $padding + $innerHeight }}" stroke="#d4d4d8" stroke-width="1" />
                    @foreach ($bars as $bar)
                        <rect x="{{ $bar['x'] }}" y="{{ $bar['y'] }}" width="32" height="{{ $bar['h'] }}" rx="4" fill="#3f3f46"></rect>
                        <text x="{{ $bar['x'] + 16 }}" y="{{ $bar['y'] - 4 }}" text-anchor="middle" font-size="10" fill="#52525b">{{ $bar['orders'] }}</text>
                        <text x="{{ $bar['x'] + 16 }}" y="{{ $chartHeight - 6 }}" text-anchor="middle" font-size="10" fill="#71717a">{{ $bar['label'] }}</text>
                    @endforeach
                </svg>
            </div>
        </div>
    </div>
@endsection
