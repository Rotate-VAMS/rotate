<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable(); // Reference to tenant
            $table->string('provider')->nullable(); // 'paypal', 'razorpay', etc.
            $table->string('payment_id')->unique(); // PayPal/Razorpay/etc. payment ID
            $table->string('order_id')->nullable(); // PayPal/Razorpay/etc. order ID
            $table->string('status')->default('created'); // created, captured, failed, refunded, etc.
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('USD');
            $table->json('payload')->nullable(); // Raw payload (for auditing/debug)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};