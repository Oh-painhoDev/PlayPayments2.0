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
        Schema::table('user_gateway_credentials', function (Blueprint $table) {
            // Allow user_id to be null for global credentials
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Before making it not nullable, we need to delete or update null user_ids
        DB::table('user_gateway_credentials')
            ->whereNull('user_id')
            ->delete();
        
        Schema::table('user_gateway_credentials', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};




