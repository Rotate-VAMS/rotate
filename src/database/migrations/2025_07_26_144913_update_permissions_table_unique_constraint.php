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
        Schema::table('permissions', function (Blueprint $table) {
            // Drop the old unique constraint
            $table->dropUnique('permissions_name_guard_name_unique');
            
            // Add the new unique constraint that includes tenant_id
            $table->unique(['tenant_id', 'name', 'guard_name'], 'permissions_tenant_id_name_guard_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('permissions_tenant_id_name_guard_name_unique');
            
            // Restore the old unique constraint
            $table->unique(['name', 'guard_name'], 'permissions_name_guard_name_unique');
        });
    }
};
