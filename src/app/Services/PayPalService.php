<?php

namespace App\Services;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    protected $client;

    public function __construct()
    {
        $env = config('services.paypal.mode') === 'live'
            ? new \PayPalCheckoutSdk\Core\ProductionEnvironment(
                config('services.paypal.client_id'),
                config('services.paypal.client_secret')
              )
            : new SandboxEnvironment(
                config('services.paypal.client_id'),
                config('services.paypal.client_secret')
              );

        $this->client = new PayPalHttpClient($env);
    }

    public function createOrder($amount, $currency = 'USD')
    {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $currency,
                    'value' => number_format($amount, 2, '.', ''),
                ]
            ]],
        ];

        return $this->client->execute($request);
    }

    public function captureOrder($orderId)
    {
        $request = new OrdersCaptureRequest($orderId);
        return $this->client->execute($request);
    }
}