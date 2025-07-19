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
        Schema::table('routes', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['fleet_id']);
            $table->dropColumn('fleet_id');
            $table->string('fleet_ids')->nullable()->after('flight_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn('fleet_ids');
            $table->string('fleet_id')->nullable()->after('flight_number');
            // Add the foreign key constraint back
            $table->foreign('fleet_id')->references('id')->on('fleets');
        });
    }
};
