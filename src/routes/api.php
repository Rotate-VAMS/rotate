<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TenantRegistrationController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\WikiController;

Route::prefix('tenant')->group(function () {
    Route::post('/pre-register', [TenantRegistrationController::class, 'preRegister']);
    Route::get('/plans', [SubscriptionController::class, 'getPlans']);
    Route::get('/check-domain/{domain}', [TenantRegistrationController::class, 'checkDomainAvailability']);
});

Route::get('/wiki', [WikiController::class, 'getWiki']);
Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
Route::post('/capture-paypal', [SubscriptionController::class, 'capturePaypal']);
Route::post('/capture-razorpay', [SubscriptionController::class, 'captureRazorpay']);