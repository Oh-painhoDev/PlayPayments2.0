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
        // Permitir user_id nullable para metas globais
        Schema::table('goals', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Voltar a exigir user_id (apenas se não houver metas globais)
        // Deletar metas globais antes de tornar user_id obrigatório
        \Illuminate\Support\Facades\DB::table('goals')
            ->whereNull('user_id')
            ->delete();
        
        Schema::table('goals', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
