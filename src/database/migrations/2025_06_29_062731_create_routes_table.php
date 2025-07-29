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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('flight_number');
            $table->unsignedBigInteger('fleet_id');
            $table->string('origin');
            $table->string('destination');
            $table->float('distance');
            $table->integer('flight_time');
            $table->unsignedBigInteger('min_rank_id');
            $table->boolean('status')->default(true);
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('fleet_id')->references('id')->on('fleet')->onDelete('cascade');
            $table->foreign('min_rank_id')->references('id')->on('ranks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
