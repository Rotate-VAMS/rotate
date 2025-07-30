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
        $name = $tenant->name;
        $domain = $tenant->domain;
        $adminEmail = $tenant->admin_email;
        $adminPassword = $tenant->admin_password;
        $adminCallsign = $tenant->admin_callsign;
        $tenant->delete();

        Artisan::call('tenant:register', [
            'name' => $name,
            'domain' => $domain,
            '--admin-email' => $adminEmail,
            '--admin-password' => $adminPassword,
            '--admin-callsign' => $adminCallsign,
        ]);

        // Fetch the new tenant
        $tenant = Tenant::where('domain', $domain)->first();

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