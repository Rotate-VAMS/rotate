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
        Schema::create('discord_pirep_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pirep_id');
            $table->string('discord_message_id');
            $table->string('discord_channel_id');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->foreign('pirep_id')->references('id')->on('pireps')->onDelete('cascade');
            $table->unique(['pirep_id', 'discord_message_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discord_pirep_messages');
    }
};
