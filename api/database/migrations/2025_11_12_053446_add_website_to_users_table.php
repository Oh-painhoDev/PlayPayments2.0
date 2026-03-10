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
            if (!Schema::hasColumn('users', 'website')) {
                // Try to add after fantasy_name if it exists, otherwise add at the end
                if (Schema::hasColumn('users', 'fantasy_name')) {
                    $table->string('website')->nullable()->after('fantasy_name');
                } else {
                    $table->string('website')->nullable();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('website');
        });
    }
};
