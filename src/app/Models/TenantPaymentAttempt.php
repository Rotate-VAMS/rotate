<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantPaymentAttempt extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'name',
        'domain',
        'admin_email',
        'admin_password',
        'admin_callsign',
        'plan_key',
        'razorpay_order_id',
        'razorpay_payment_id',
        'status',
        'razorpay_response',
    ];

    protected $table = 'tenant_payment_attempts';
}
