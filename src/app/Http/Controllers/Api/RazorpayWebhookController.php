<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Artisan;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $razorpaySubscriptionId = $payload['payload']['payment']['entity']['order_id'] ?? null;

        $tenant = Tenant::where('razorpay_subscription_id', $razorpaySubscriptionId)->first();

        Artisan::call('tenant:register', [
            'name' => $tenant->name,
            'domain' => $tenant->domain,
            '--admin-email' => $tenant->admin_email,
            '--admin-password' => $tenant->admin_password,
            '--admin-callsign' => $tenant->admin_callsign,
            '--force' => true,
        ]);

        if ($tenant) {
            app(SubscriptionService::class)->activatePlan($tenant, $tenant->plan_key, $razorpaySubscriptionId);
        }

        return response()->json(['status' => 'ok']);
    }
}