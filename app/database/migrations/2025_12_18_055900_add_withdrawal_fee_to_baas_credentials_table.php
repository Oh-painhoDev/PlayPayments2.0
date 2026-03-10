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
        Schema::table('baas_credentials', function (Blueprint $table) {
            $table->decimal('withdrawal_fee', 10, 2)->default(0.00)->after('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('baas_credentials', function (Blueprint $table) {
            $table->dropColumn('withdrawal_fee');
        });
    }
};

