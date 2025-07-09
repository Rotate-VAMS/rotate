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
        Schema::table('users', function (Blueprint $table) {
            $table->string('callsign')->nullable()->after('name');
            $table->boolean('status')->default(true)->after('callsign');
            $table->unsignedBigInteger('rank_id')->nullable()->after('status');
            $table->integer('flying_hours')->default(0)->after('rank_id');

            $table->foreign('rank_id')->references('id')->on('ranks')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('callsign');
            $table->dropColumn('status');
            $table->dropColumn('rank_id');
            $table->dropColumn('flying_hours');
            $table->dropSoftDeletes();
        });
    }
};
