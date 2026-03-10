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
        Schema::create('pix_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['EMAIL', 'CPF', 'CNPJ', 'PHONE', 'EVP'])->comment('Tipo da chave PIX');
            $table->string('key', 255)->comment('Valor da chave PIX');
            $table->string('description', 255)->nullable()->comment('Descrição opcional da chave');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('Status da chave');
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'type']);
            $table->index('key'); // Índice para busca rápida, mas não único (permitir mesma chave para usuários diferentes)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pix_keys');
    }
};
