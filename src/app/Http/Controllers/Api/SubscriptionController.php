<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SubscriptionService;
use App\Services\PayPalService;
use App\Models\Payment;
use App\Models\Tenant;
use App\Services\RazorpayService;

class SubscriptionController extends Controller
{
    public function getPlans()
    {
        return response()->json(config('plans'));
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_key' => 'required|in:' . implode(',', array_keys(config('plans'))),
            'gateway' => 'required|in:paypal,razorpay',
        ]);
    
        $plan = config('plans.' . $request->plan_key);
        $amount = $plan['amount']; // always in USD
    
        if ($request->gateway === 'paypal') {
            try {
                $response = app(PayPalService::class)->createOrder($amount);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'PayPal order failed.'], 500);
            }
            $order = $response->result;
    
            return response()->json([
                'gateway' => 'paypal',
                'order_id' => $order->id,
                'approve_url' => collect($order->links)->firstWhere('rel', 'approve')->href,
                'status' => $order->status,
            ]);
        }
    
        if ($request->gateway === 'razorpay') {
            $amountInInr = round($amount * config('services.razorpay.usd_to_inr_rate', 83), 2);
    
            try {
                $order = app(RazorpayService::class)->createOrder($amountInInr);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Razorpay order failed.'], 500);
            }
    
            return response()->json([
                'gateway' => 'razorpay',
                'order_id' => $order['id'],
                'amount' => $order['amount'],
                'currency' => $order['currency'],
            ]);
        }
    }

    public function capturePaypal(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
            'plan_key' => 'required|in:' . implode(',', array_keys(config('plans'))),
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        $tenant = Tenant::find((int)$request->tenant_id);
        $result = app(PayPalService::class)->captureOrder($request->order_id);

        $capture = $result->result->purchase_units[0]->payments->captures[0];

        // Save payment
        Payment::create([
            'tenant_id' => $tenant->id,
            'provider' => 'paypal',
            'payment_id' => $capture->id,
            'order_id' => $request->order_id,
            'status' => $capture->status,
            'amount' => $capture->amount->value,
            'currency' => $capture->amount->currency_code,
            'payload' => json_encode($capture),
        ]);

        // Activate plan
        app(SubscriptionService::class)->activatePlan($tenant, $request->plan_key, $capture->id);

        return response()->json(['status' => 'success']);
    }

    public function captureRazorpay(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|string',
            'order_id' => 'required|string',
            'plan_key' => 'required|in:' . implode(',', array_keys(config('plans'))),
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        $tenant = Tenant::find((int)$request->tenant_id);
        $plan = config('plans.' . $request->plan_key);

        $razorpayService = app(RazorpayService::class);
        $payment = $razorpayService->fetchPayment($request->payment_id);

        if ($payment['status'] !== 'captured') {
            return response()->json(['status' => 'error', 'message' => 'Payment not captured'], 400);
        }

        // Save payment (already captured by Razorpay client)
        Payment::create([
            'tenant_id' => $tenant->id,
            'provider' => 'razorpay',
            'payment_id' => $request->payment_id,
            'order_id' => $request->order_id,
            'status' => 'captured',
            'amount' => round($plan['amount'] * config('services.razorpay.usd_to_inr_rate', 83), 2),
            'currency' => 'INR',
            'payload' => json_encode($request->all()),
        ]);

        app(SubscriptionService::class)->activatePlan($tenant, $request->plan_key, $request->payment_id);

        return response()->json(['status' => 'success']);
    }
}