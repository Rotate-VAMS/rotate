<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TenantRegistrationController;
use App\Http\Controllers\Api\RazorpayWebhookController;
use App\Http\Controllers\Api\SubscriptionController;

Route::prefix('tenant')->group(function () {
    Route::post('/pre-register', [TenantRegistrationController::class, 'preRegister']);
    Route::post('/webhook', [RazorpayWebhookController::class, 'handle']);
    Route::get('/plans', [SubscriptionController::class, 'getPlans']);
    Route::get('/check-domain/{domain}', [TenantRegistrationController::class, 'checkDomainAvailability']);
});