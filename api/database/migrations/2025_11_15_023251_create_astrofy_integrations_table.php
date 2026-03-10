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
        Schema::create('astrofy_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('name'); // Nome da integração
            $table->string('gateway_key'); // X-Gateway-Key da Astrofy (chave que a Astrofy nos dá)
            $table->string('base_url'); // URL base do nosso sistema onde a Astrofy vai chamar
            $table->json('payment_types')->default('["PIX"]'); // Tipos de pagamento suportados (apenas PIX por enquanto)
            $table->boolean('is_active')->default(true); // Status ativo/inativo
            $table->timestamps();
            
            // Índices
            $table->index('user_id');
            $table->index('is_active');
            $table->index('gateway_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('astrofy_integrations');
    }
};
