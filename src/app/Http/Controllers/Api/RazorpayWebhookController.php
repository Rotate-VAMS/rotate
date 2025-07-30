<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use App\Mail\TenantRegistrationMail;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $signature = $request->header('X-Razorpay-Signature');
        $secret = env('RAZORPAY_WEBHOOK_SECRET');
        $body = $request->getContent();
    
        if (!hash_equals(hash_hmac('sha256', $body, $secret), $signature)) {
            Log::warning('Razorpay signature verification failed');
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        $payload = $request->all();
        $event = $payload['event'] ?? null;
    
        switch ($event) {
            case 'payment.captured':
                $this->handlePaymentCaptured($payload);
                break;
    
            case 'payment.failed':
                $this->handlePaymentFailed($payload);
                break;
    
            default:
                Log::info("Unhandled Razorpay event: $event", $payload);
                break;
        }
    
        return response()->json(['status' => 'ok']);
    }
    
    protected function handlePaymentCaptured(array $payload)
    {
        $paymentData = $payload['payload']['payment']['entity'];
    
        $payment = new Payment();
        $payment->razorpay_payment_id = $paymentData['id'];
        $payment->razorpay_order_id = $paymentData['order_id'] ?? null;
        $payment->amount = $paymentData['amount'] / 100;
        $payment->currency = $paymentData['currency'];
        $payment->status = $paymentData['status'];
        $payment->payload = $paymentData;
        $payment->save();
    
        $tenant = Tenant::where('razorpay_order_id', $paymentData['order_id'])->first();
        if ($tenant) {
            app(SubscriptionService::class)->activatePlan($tenant, $tenant->plan_key, $payment->razorpay_payment_id);
            $this->sendWelcomeEmail($tenant);
        }
    }
    
    protected function handlePaymentFailed(array $payload)
    {
        $paymentData = $payload['payload']['payment']['entity'];
    
        $payment = new Payment();
        $payment->razorpay_payment_id = $paymentData['id'];
        $payment->razorpay_order_id = $paymentData['order_id'] ?? null;
        $payment->amount = $paymentData['amount'] / 100;
        $payment->currency = $paymentData['currency'];
        $payment->status = $paymentData['status'];
        $payment->payload = $paymentData;
        $payment->save();
    
        Log::warning("Payment failed for order: {$paymentData['order_id']}", $paymentData);
    }

    /**
     * Send welcome email to the admin user after successful payment
     */
    private function sendWelcomeEmail(Tenant $tenant): void
    {
        $adminUser = User::where('tenant_id', $tenant->id)
                        ->where('email', $tenant->admin_email)
                        ->first();

        if ($adminUser) {
            try {
                Mail::to($adminUser->email)->send(new TenantRegistrationMail($tenant, $adminUser, ''));
            } catch (\Exception $e) {
                Log::error('Failed to send welcome email: ' . $e->getMessage());
            }
        }
    }
}