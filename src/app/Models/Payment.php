<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'tenant_id',
        'razorpay_payment_id',
        'razorpay_order_id',
        'status',
        'amount',
        'currency',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
