<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Payment\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function __construct(
        private readonly RazorpayService $razorpay,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        if (! $this->hasValidSignature($request)) {
            return response()->json(['ok' => false, 'message' => 'Invalid signature'], 401);
        }

        $reference = (string) $request->input('reference', $request->input('payload.order.entity.receipt', ''));

        if ($reference === '') {
            return response()->json(['ok' => false], 422);
        }

        $this->razorpay->markPaidFromWebhook($reference, $request->string('payment_id')->toString() ?: null);

        return response()->json(['ok' => true]);
    }

    private function hasValidSignature(Request $request): bool
    {
        $secret = (string) config('services.razorpay.webhook_secret', '');
        if ($secret === '') {
            // Local development fallback.
            return true;
        }

        $provided = (string) $request->header('X-Razorpay-Signature', '');
        if ($provided === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);
        $valid = hash_equals($expected, $provided);

        if (! $valid) {
            Log::warning('Razorpay webhook signature mismatch');
        }

        return $valid;
    }
}
