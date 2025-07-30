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
            'amount'          => $plan['amount'] * 100,
            'currency'        => 'INR',
            'payment_capture' => 1,
        ]);

        return $order;
    }

    public function activatePlan(Tenant $tenant, string $planKey, ?string $razorpayPaymentId = null)
    {
        // Delete the temp tenant first then re-create the propper tenant
        $tenant->delete();

        Artisan::call('tenant:register', [
            'name' => $tenant->name,
            'domain' => $tenant->domain,
            '--admin-email' => $tenant->admin_email,
            '--admin-password' => 12345678,
            '--admin-callsign' => $tenant->admin_callsign,
        ]);

        // Fetch the new tenant
        $tenant = Tenant::where('domain', $tenant->domain)->first();

        // Update the payments table with new tenant ID
        $payment = Payment::where('razorpay_payment_id', $razorpayPaymentId)->first();
        $payment->tenant_id = $tenant->id;
        $payment->save();

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
    }
}