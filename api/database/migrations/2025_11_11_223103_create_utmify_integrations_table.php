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
        Schema::create('utmify_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name'); // Nome da integração
            $table->string('api_token'); // Token da API UTMify
            $table->string('pixel_id')->nullable(); // ID do Pixel (opcional)
            $table->boolean('trigger_on_payment')->default(true); // Acionar no pagamento
            $table->boolean('trigger_on_creation')->default(true); // Acionar na criação
            $table->boolean('is_active')->default(true); // Status ativo/inativo
            $table->timestamps();
            
            // Índices
            $table->index('user_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utmify_integrations');
    }
};
