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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('name'); // Nome da meta (ex: "Faturamento")
            $table->string('type')->default('vendas'); // Tipo/cargo (ex: "vendas", "clientes", "transacoes")
            $table->decimal('target_value', 15, 2); // Valor da meta
            $table->string('period')->default('monthly'); // monthly, yearly, custom
            $table->date('start_date')->nullable(); // Data de início (para período custom)
            $table->date('end_date')->nullable(); // Data de fim (para período custom)
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0); // Ordem de exibição
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('user_id');
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
