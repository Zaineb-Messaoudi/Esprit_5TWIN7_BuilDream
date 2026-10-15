<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Stripe Connect webhook controller.
 */
class StripeWebhookController
{
    public function __construct(private readonly \App\Services\StripeConnectService $service) {}

    /**
     * Handle incoming Stripe webhook.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        if (! $sigHeader) {
            return response()->json(['error' => 'Missing Stripe-Signature header'], 400);
        }

        try {
            $this->service->handleWebhook($payload, $request->header('Stripe-Signature'));

            return response()->json(['received' => true]);
        } catch (\Exception $e) {
            \Log::error('Stripe webhook error: '.$e->getMessage());

            return response()->json(['error' => 'Webhook processing failed'], 400);
        }
    }
}
