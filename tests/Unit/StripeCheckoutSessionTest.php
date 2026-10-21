<?php

namespace Tests\Unit;

use App\Services\StripeConnectService;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Tests\TestCase;

class StripeCheckoutSessionTest extends TestCase
{
    public function test_reservation_checkout_disables_managed_payments_for_products_without_tax_codes(): void
    {
        config(['services.stripe.secret' => 'sk_test_placeholder']);

        $httpClient = new class implements ClientInterface
        {
            public array $requestParameters = [];

            public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null)
            {
                $this->requestParameters = $params;

                return [
                    '{"id":"cs_test_checkout","object":"checkout.session","url":"https://checkout.stripe.com/test","mode":"payment","status":"open"}',
                    200,
                    ['Request-Id' => 'req_test'],
                ];
            }
        };

        ApiRequestor::setHttpClient($httpClient);

        try {
            $session = app(StripeConnectService::class)->createCheckoutSession(
                10.00,
                'usd',
                'RES-TEST1234',
                1,
                2,
                'https://example.test/success',
                'https://example.test/cancel',
            );

            $this->assertSame('cs_test_checkout', $session->id);
            $this->assertSame(['enabled' => 'false'], $httpClient->requestParameters['managed_payments']);
        } finally {
            ApiRequestor::setHttpClient(null);
        }
    }
}
