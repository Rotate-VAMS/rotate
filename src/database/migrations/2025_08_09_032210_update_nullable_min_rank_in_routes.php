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
            $table->dropForeign(['min_rank_id']);
            
            // Change the column to nullable while keeping the correct type
            $table->unsignedBigInteger('min_rank_id')->nullable()->change();
            
            // Recreate the foreign key constraint
            $table->foreign('min_rank_id')->references('id')->on('ranks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['min_rank_id']);
            
            // Change the column back to not nullable
            $table->unsignedBigInteger('min_rank_id')->nullable(false)->change();
            
            // Recreate the foreign key constraint
            $table->foreign('min_rank_id')->references('id')->on('ranks')->onDelete('cascade');
        });
    }
};
