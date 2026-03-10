<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primeiro, deletar ou atribuir metas sem user_id
        // Se houver metas globais (user_id null), podemos:
        // 1. Deletá-las (mais seguro)
        // 2. Atribuí-las ao primeiro usuário admin (não recomendado)
        // 3. Atribuí-las a usuários específicos baseado em algum critério
        
        // Por segurança, vamos deletar metas globais sem user_id
        // pois não há como saber a qual usuário pertencem
        DB::table('goals')
            ->whereNull('user_id')
            ->delete();
        
        // Agora tornar user_id obrigatório
        Schema::table('goals', function (Blueprint $table) {
            // Remover nullable do user_id
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            // Voltar a permitir nullable
            $table->foreignId('user_id')->nullable()->change();
        });
    }
};




