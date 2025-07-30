<?php

namespace App\Services;

use App\Models\Tenant;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Artisan;

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

    public function activatePlan(Tenant $tenant, string $planKey, ?string $razorpaySubscriptionId = null)
    {
        \Illuminate\Support\Facades\Log::info('SubscriptionService activatePlan started', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'plan_key' => $planKey
        ]);
        
        // Check if tenant already has a user (meaning it was already registered)
        $existingUser = \App\Models\User::where('tenant_id', $tenant->id)->first();
        
        if (!$existingUser) {
            \Illuminate\Support\Facades\Log::info('No existing user found, running tenant:register command');
            // Only run tenant:register if no user exists (fresh tenant)
            \Illuminate\Support\Facades\Artisan::call('tenant:register', [
                'name' => $tenant->name,
                'domain' => $tenant->domain,
                '--admin-email' => $tenant->admin_email,
                '--admin-password' => $tenant->admin_password,
                '--admin-callsign' => $tenant->admin_callsign,
            ]);
        } else {
            \Illuminate\Support\Facades\Log::info('Existing user found, skipping tenant:register command', [
                'user_id' => $existingUser->id,
                'user_email' => $existingUser->email
            ]);
        }

        $plan = config('plans.' . $planKey);
        
        if (!$plan) {
            \Illuminate\Support\Facades\Log::error('Plan not found in config', ['plan_key' => $planKey]);
            throw new \Exception("Plan '{$planKey}' not found in configuration");
        }

        $interval = $plan['interval'] ?? null;
        $validUntil = match ($interval) {
            'monthly' => now()->addMonth(),
            'yearly' => now()->addYear(),
            default => null,
        };

        \Illuminate\Support\Facades\Log::info('Updating tenant with plan details', [
            'plan_key' => $planKey,
            'valid_until' => $validUntil,
            'subscription_id' => $razorpaySubscriptionId
        ]);

        $tenant->update([
            'plan_key' => $planKey,
            'plan_valid_until' => $validUntil,
            'razorpay_subscription_id' => $razorpaySubscriptionId,
        ]);
        
        \Illuminate\Support\Facades\Log::info('SubscriptionService activatePlan completed successfully');
    }
}