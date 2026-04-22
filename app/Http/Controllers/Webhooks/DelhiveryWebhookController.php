<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Shipping\DelhiveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DelhiveryWebhookController extends Controller
{
    public function __construct(
        private readonly DelhiveryService $delhivery,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        if (! $this->hasValidToken($request)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $waybill = (string) $request->input('waybill', $request->input('AWB', ''));

        if ($waybill === '') {
            return response()->json(['ok' => false], 422);
        }

        $this->delhivery->applyTrackingUpdate($waybill, $request->all());

        return response()->json(['ok' => true]);
    }

    private function hasValidToken(Request $request): bool
    {
        $token = (string) config('services.delhivery.webhook_token', '');
        if ($token === '') {
            // Local development fallback.
            return true;
        }

        $headerToken = (string) $request->header('X-Delhivery-Token', '');
        $auth = (string) $request->header('Authorization', '');
        $bearer = str_starts_with($auth, 'Bearer ') ? substr($auth, 7) : '';

        return hash_equals($token, $headerToken) || ($bearer !== '' && hash_equals($token, $bearer));
    }
}
