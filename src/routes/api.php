<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TenantRegistrationController;
use App\Http\Controllers\Api\RazorpayWebhookController;
use App\Http\Controllers\Api\SubscriptionController;

Route::post('/razorpay/webhook', [RazorpayWebhookController::class, 'handle']);

// Test route to verify webhook endpoint is accessible
Route::get('/razorpay/webhook/test', function () {
    return response()->json(['status' => 'webhook endpoint accessible']);
});

Route::prefix('tenant')->group(function () {
    Route::post('/pre-register', [TenantRegistrationController::class, 'preRegister']);
    Route::get('/plans', [SubscriptionController::class, 'getPlans']);
    Route::get('/check-domain/{domain}', [TenantRegistrationController::class, 'checkDomainAvailability']);
});