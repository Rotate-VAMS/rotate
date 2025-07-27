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
        Schema::create('leaderboard_points_configuration', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants');
            $table->string('leaderboard_event_name');
            $table->integer('points');
            $table->timestamps();
            $table->softDeletes();
        });

        // Add points to users table
        Schema::table('users', function (Blueprint $table) {
            $table->integer('points')->default(0)->after('rank_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboard_points_configuration');
    }
};
