<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $signature = $request->header('X-Razorpay-Signature');
        $secret = env('RAZORPAY_WEBHOOK_SECRET');

        $body = $request->getContent();

        if (!hash_equals(
            hash_hmac('sha256', $body, $secret),
            $signature
        )) {
            Log::warning('Razorpay signature verification failed');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = $request->all();
        $event = $payload['event'] ?? null;

        if ($event === 'payment.captured') {
            $paymentData = $payload['payload']['payment']['entity'];

            $payment = new Payment();
            $payment->razorpay_payment_id = $paymentData['id'];
            $payment->razorpay_order_id = $paymentData['order_id'] ?? null;
            $payment->amount = $paymentData['amount'] / 100;
            $payment->currency = $paymentData['currency'];
            $payment->status = $paymentData['status'];
            $payment->payload = $paymentData;
            $payment->save();

            $tenant = Tenant::where('razorpay_order_id', $paymentData['order_id'])->first();
            if ($tenant) {
                app(SubscriptionService::class)->activatePlan($tenant, $tenant->plan_key, $payment->razorpay_payment_id);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}