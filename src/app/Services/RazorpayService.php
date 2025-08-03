<?php

namespace App\Services;

use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected $api;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key_id'),
            config('services.razorpay.key_secret')
        );
    }

    /**
     * Create a Razorpay order
     */
    public function createOrder(float $amountInInr): array
    {
        $order = $this->api->order->create([
            'receipt' => 'rcpt_' . uniqid(),
            'amount' => $amountInInr * 100, // in paise
            'currency' => 'INR',
            'payment_capture' => 1,
        ]);

        return $order->toArray();
    }

    /**
     * Fetch a Razorpay payment
     */
    public function fetchPayment(string $paymentId): array
    {
        return $this->api->payment->fetch($paymentId)->toArray();
    }
}