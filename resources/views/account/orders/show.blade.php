<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Order tracking</p>
                <h2 class="text-xl font-bold leading-tight text-zinc-900">Track order</h2>
            </div>
            <p class="text-sm font-semibold text-zinc-600">{{ $order->reference }}</p>
        </div>
    </x-slot>

    <div class="bg-gradient-to-b from-zinc-100 to-zinc-50 py-10">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif

            @if ($tracking['is_cancelled'])
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                    <p class="font-semibold">This order was cancelled.</p>
                    <p class="mt-1 text-rose-800">If you were charged, refunds follow your payment method’s policy.</p>
                </div>
            @elseif (! empty($tracking['delay']['active']))
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                    <p class="font-semibold">Delivery is delayed</p>
                    @if (! empty($tracking['delay']['reason']))
                        <p class="mt-1">{{ $tracking['delay']['reason'] }}</p>
                    @endif
                    @if (! empty($tracking['delay']['days']))
                        <p class="mt-1 text-amber-900">Updated estimate: about {{ $tracking['delay']['days'] }} extra day(s).</p>
                    @endif
                </div>
            @endif

            {{-- Progress bar (Amazon-style) --}}
            @unless ($tracking['is_cancelled'])
                @php
                    $pct = $tracking['current_index'] >= 0
                        ? min(100, (int) round((($tracking['current_index'] + 1) / max(1, count($tracking['steps']))) * 100))
                        : 0;
                @endphp
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900">Shipment progress</h3>
                            <p class="mt-1 text-sm text-zinc-600">Live updates as your package moves — same idea as Flipkart / Amazon.</p>
                        </div>
                        <div class="hidden text-right text-sm text-zinc-600 sm:block">
                            @if ($tracking['waybill'])
                                <p class="font-mono text-xs text-zinc-900">AWB {{ $tracking['waybill'] }}</p>
                            @endif
                            @if ($tracking['carrier_status'])
                                <p class="mt-0.5 text-xs capitalize">{{ $tracking['carrier_status'] }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="mt-5 h-2 w-full overflow-hidden rounded-full bg-zinc-100">
                        <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $pct }}%"></div>
                    </div>

                    {{-- Desktop: horizontal milestones (Amazon-style row) --}}
                    <div class="mt-8 hidden md:grid md:grid-cols-5 md:gap-3">
                        @foreach ($tracking['steps'] as $step)
                            @php
                                $isDone = $step['state'] === 'done';
                                $isCurrent = $step['state'] === 'current';
                            @endphp
                            <div class="rounded-xl border {{ $isCurrent ? 'border-emerald-300 bg-emerald-50/60' : 'border-zinc-100 bg-zinc-50/60' }} px-3 py-4 text-center">
                                <div class="mx-auto flex h-9 w-9 items-center justify-center rounded-full {{ $isDone || $isCurrent ? 'bg-emerald-600 text-white shadow-sm' : 'bg-zinc-200 text-zinc-500' }}">
                                    @if ($isDone)
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    @elseif ($isCurrent)
                                        <span class="h-2 w-2 animate-pulse rounded-full bg-white"></span>
                                    @else
                                        <span class="text-[10px] font-bold">{{ $loop->iteration }}</span>
                                    @endif
                                </div>
                                <p class="mt-3 text-xs font-semibold text-zinc-900">{{ $step['title'] }}</p>
                                <p class="mt-1 text-[11px] leading-snug text-zinc-500">{{ $step['detail'] }}</p>
                                @if ($step['at'])
                                    <p class="mt-2 text-[11px] text-zinc-400">{{ $step['at']->timezone(config('app.timezone'))->format('M j, g:i A') }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Mobile vertical timeline (Flipkart-style) --}}
                    <ol class="mt-6 space-y-4 md:hidden">
                        @foreach ($tracking['steps'] as $step)
                            @php
                                $isDone = $step['state'] === 'done';
                                $isCurrent = $step['state'] === 'current';
                            @endphp
                            <li class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $isDone || $isCurrent ? 'bg-emerald-600 text-white' : 'bg-zinc-200 text-zinc-500' }}">
                                        @if ($isDone)
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <span class="text-xs font-bold">{{ $loop->iteration }}</span>
                                        @endif
                                    </span>
                                    @unless ($loop->last)
                                        <span class="mt-1 h-full min-h-[1.25rem] w-0.5 grow {{ $isDone ? 'bg-emerald-400' : 'bg-zinc-200' }}"></span>
                                    @endunless
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-zinc-900">{{ $step['title'] }}</p>
                                    <p class="text-xs text-zinc-600">{{ $step['detail'] }}</p>
                                    @if ($step['at'])
                                        <p class="mt-1 text-xs text-zinc-400">{{ $step['at']->timezone(config('app.timezone'))->format('M j, Y · g:i A') }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    @if ($tracking['waybill'])
                        <div class="mt-6 rounded-md border border-dashed border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-700">
                            <p><span class="font-semibold text-zinc-900">Tracking ID:</span> {{ $tracking['waybill'] }}</p>
                            <p class="mt-1 text-xs text-zinc-500">When Delhivery (or your carrier) is connected, deep links and SMS updates use this AWB.</p>
                        </div>
                    @endif
                </div>
            @endunless

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-zinc-900">Order details</h3>
                    <dl class="mt-4 space-y-2 text-sm text-zinc-700">
                        <div class="flex justify-between gap-4">
                            <dt class="text-zinc-500">Payment</dt>
                            <dd class="font-medium text-zinc-900">{{ $order->payment_status->value }} · {{ str_replace('_', ' ', $order->payment_mode->value) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-zinc-500">Ordered on</dt>
                            <dd class="font-medium text-zinc-900">{{ $order->placed_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-zinc-900">Items</h3>
                    <ul class="mt-3 divide-y divide-zinc-100">
                        @foreach ($order->items as $item)
                            @php $thumb = $item->variant?->product?->images?->first(); @endphp
                            <li class="flex gap-3 py-3 first:pt-0">
                                <div class="shrink-0">
                                    @if ($thumb)
                                        <img src="{{ $thumb->url() }}" alt="" class="h-14 w-14 rounded-md border border-zinc-100 object-cover" loading="lazy" width="56" height="56">
                                    @else
                                        <div class="flex h-14 w-14 items-center justify-center rounded-md border border-dashed border-zinc-200 bg-zinc-50 text-[9px] text-zinc-400">—</div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1 text-sm text-zinc-700">
                                    <p class="font-medium text-zinc-900">{{ $item->product_name }}</p>
                                    <p class="text-zinc-500">{{ $item->variant_label }} × {{ $item->quantity }}</p>
                                </div>
                                <span class="shrink-0 self-start font-semibold text-zinc-900">₹{{ number_format($item->line_total, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-4 text-right text-base font-bold text-zinc-900">Total ₹{{ number_format($order->grand_total, 2) }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-zinc-900">All updates</h3>
                <p class="mt-1 text-sm text-zinc-600">New rows appear when the warehouse, carrier, or webhooks change status — same flow as “Track package” on large marketplaces.</p>
                <ul class="mt-4 divide-y divide-zinc-100">
                    @forelse ($tracking['activity'] as $row)
                        <li class="flex gap-4 py-3">
                            <div class="w-28 shrink-0 text-xs text-zinc-500">
                                {{ $row['time']?->timezone(config('app.timezone'))->format('M j') }}<br>
                                {{ $row['time']?->timezone(config('app.timezone'))->format('g:i A') }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-zinc-900">{{ $row['title'] }}</p>
                                @if ($row['detail'])
                                    <p class="mt-0.5 text-sm text-zinc-600">{{ $row['detail'] }}</p>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="py-4 text-sm text-zinc-600">No status updates yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
