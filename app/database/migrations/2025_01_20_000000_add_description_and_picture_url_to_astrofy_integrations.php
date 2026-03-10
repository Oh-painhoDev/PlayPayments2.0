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
        Schema::table('astrofy_integrations', function (Blueprint $table) {
            $table->text('description')->nullable()->after('payment_types');
            $table->string('picture_url')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('astrofy_integrations', function (Blueprint $table) {
            $table->dropColumn(['description', 'picture_url']);
        });
    }
};

