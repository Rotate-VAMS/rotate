<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('plan_key')->nullable();
            $table->date('plan_valid_until')->nullable();
            $table->string('razorpay_order_id')->nullable();
            $table->string('admin_email')->nullable();
            $table->string('admin_password')->nullable();
            $table->string('admin_callsign')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('plan_key');
            $table->dropColumn('plan_valid_until');
            $table->dropColumn('razorpay_order_id');
            $table->dropColumn('admin_email');
            $table->dropColumn('admin_password');
            $table->dropColumn('admin_callsign');
        });
    }
};
