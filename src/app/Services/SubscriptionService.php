<?php

namespace App\Services;

use App\Models\Tenant;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Artisan;
use App\Models\Payment;

class SubscriptionService
{
    public function createOrder(Tenant $tenant, string $planKey)
    {
        $plan = config('plans.' . $planKey);
        if (!$plan) {
            throw new \Exception('Invalid plan selected');
        }

        $api = new Api(config('services.razorpay.key_id'), config('services.razorpay.key_secret'));

        $order = $api->order->create([
            'receipt'         => 'rcpt_' . uniqid(),
            'amount'          => $plan['amount'],
            'currency'        => 'INR',
            'payment_capture' => 1,
        ]);

        return $order;
    }

    public function activatePlan(Tenant $tenant, string $planKey, ?string $paymentId = null): void
    {
        $plan = config('plans.' . $planKey);
    
        $interval = $plan['interval'] ?? null;
        $validUntil = match ($interval) {
            'monthly' => now()->addMonth(),
            'yearly' => now()->addYear(),
            default => null,
        };
    
        $tenant->update([
            'plan_key' => $planKey,
            'plan_valid_until' => $validUntil,
        ]);
    
        if ($paymentId) {
            Payment::where('payment_id', $paymentId)->update(['tenant_id' => $tenant->id]);
        }
    }
}