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
        Schema::table('payment_gateways', function (Blueprint $table) {
            // Campo para relacionar sub-adquirente com adquirente base
            $table->unsignedBigInteger('parent_gateway_id')->nullable()->after('id');
            
            // Nome do webhook para sub-adquirentes (ex: "sharkpay", "shieldtecnologia")
            $table->string('webhook_name')->nullable()->after('slug');
            
            // Identificar se é adquirente base
            $table->boolean('is_base')->default(false)->after('is_default');
            
            // Identificar se é whitelabel/sub-adquirente
            $table->boolean('is_whitelabel')->default(false)->after('is_base');
            
            // Foreign key para parent_gateway_id
            $table->foreign('parent_gateway_id')
                ->references('id')
                ->on('payment_gateways')
                ->onDelete('cascade');
            
            // Índice para melhor performance
            $table->index('parent_gateway_id');
            $table->index('webhook_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->dropForeign(['parent_gateway_id']);
            $table->dropIndex(['parent_gateway_id']);
            $table->dropIndex(['webhook_name']);
            $table->dropColumn(['parent_gateway_id', 'webhook_name', 'is_base', 'is_whitelabel']);
        });
    }
};

