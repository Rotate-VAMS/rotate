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
        Schema::table('custom_field_configuration', function (Blueprint $table) {
            $table->dropColumn('aggregation_type');
            $table->integer('dropdown_value_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_field_configuration', function (Blueprint $table) {
            $table->integer('aggregation_type')->nullable();
            $table->dropColumn('dropdown_value_type');
        });
    }
};
