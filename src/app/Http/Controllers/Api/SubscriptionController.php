<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SubscriptionService;

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
        ]);

        $tenant = app('currentTenant');

        $order = app(SubscriptionService::class)->createOrder($tenant, $request->plan_key);

        return response()->json([
            'order_id' => $order['id'],
            'amount' => $order['amount'],
            'currency' => $order['currency'],
            'plan_key' => $request->plan_key,
        ]);
    }
}