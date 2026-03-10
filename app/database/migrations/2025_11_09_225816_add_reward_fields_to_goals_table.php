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
        Schema::table('goals', function (Blueprint $table) {
            $table->string('reward_type')->nullable()->after('description'); // 'cash', 'bonus', 'discount', 'custom'
            $table->decimal('reward_value', 15, 2)->nullable()->after('reward_type'); // Valor do prêmio (R$)
            $table->text('reward_description')->nullable()->after('reward_value'); // Descrição do prêmio
            $table->boolean('auto_reward')->default(false)->after('reward_description'); // Se deve premiar automaticamente
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn(['reward_type', 'reward_value', 'reward_description', 'auto_reward']);
        });
    }
};
