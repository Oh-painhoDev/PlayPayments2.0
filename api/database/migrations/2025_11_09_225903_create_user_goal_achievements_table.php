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
        Schema::create('user_goal_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('goal_id')->constrained('goals')->onDelete('cascade');
            $table->decimal('achieved_value', 15, 2); // Valor alcançado quando bateu a meta
            $table->decimal('reward_value', 15, 2)->nullable(); // Valor do prêmio recebido
            $table->string('reward_type')->nullable(); // Tipo do prêmio
            $table->text('reward_description')->nullable(); // Descrição do prêmio
            $table->boolean('reward_given')->default(false); // Se o prêmio foi dado
            $table->timestamp('reward_given_at')->nullable(); // Quando o prêmio foi dado
            $table->text('notes')->nullable(); // Notas adicionais
            $table->timestamps();
            
            // Índices
            $table->index('user_id');
            $table->index('goal_id');
            $table->index('reward_given');
            $table->unique(['user_id', 'goal_id', 'created_at']); // Evitar duplicatas no mesmo período
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_goal_achievements');
    }
};
