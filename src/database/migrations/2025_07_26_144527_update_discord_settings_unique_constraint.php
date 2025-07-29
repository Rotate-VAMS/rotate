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
        Schema::table('discord_settings', function (Blueprint $table) {
            // Drop the old unique constraint
            $table->dropUnique('discord_settings_setting_key_unique');
            
            // Add the new unique constraint that includes tenant_id
            $table->unique(['tenant_id', 'setting_key'], 'discord_settings_tenant_id_setting_key_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discord_settings', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('discord_settings_tenant_id_setting_key_unique');
            
            // Restore the old unique constraint
            $table->unique('setting_key', 'discord_settings_setting_key_unique');
        });
    }
};
