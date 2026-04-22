<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\Order\OrderTrackingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly OrderTrackingService $tracking,
    ) {}

    public function index(Request $request): View
    {
        return view('account.orders.index', [
            'orders' => $this->orders->forUser((int) $request->user()->id),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        abort_if($order->user_id !== $request->user()->id, 404);

        $model = $this->orders->find($order->id);

        abort_if(! $model, 404);

        return view('account.orders.show', [
            'order' => $model,
            'tracking' => $this->tracking->build($model),
        ]);
    }
}
