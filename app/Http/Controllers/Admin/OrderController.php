<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\AdminOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly AdminOrderService $orders,
    ) {}

    public function index(Request $request): View
    {
        return view('admin.orders.index', [
            'orders' => $this->orders->paginate($request->only(['status', 'from', 'to'])),
            'filters' => $request->only(['status', 'from', 'to']),
        ]);
    }

    public function show(Order $order): View
    {
        $model = $this->orders->find($order->id);

        abort_if(! $model, 404);

        return view('admin.orders.show', ['order' => $model]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $model = $this->orders->find($order->id);

        abort_if(! $model, 404);

        $data = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $this->orders->updateStatus($model, $data['status']);

        return back()->with('status', 'Order status updated.');
    }

    public function delay(Request $request, Order $order): RedirectResponse
    {
        $model = $this->orders->find($order->id);

        abort_if(! $model, 404);

        $data = $request->validate([
            'delay_reason' => ['required', 'string', 'max:2000'],
            'delay_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $this->orders->markDelayed($model, $data['delay_reason'], (int) $data['delay_days']);

        return back()->with('status', 'Order marked as delayed.');
    }
}
