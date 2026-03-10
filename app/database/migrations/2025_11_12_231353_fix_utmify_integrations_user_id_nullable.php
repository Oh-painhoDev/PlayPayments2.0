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
        // Primeiro, remover a foreign key constraint
        Schema::table('utmify_integrations', function (Blueprint $table) {
            // Encontrar o nome da constraint
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'utmify_integrations' 
                AND COLUMN_NAME = 'user_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            if (!empty($foreignKeys)) {
                $constraintName = $foreignKeys[0]->CONSTRAINT_NAME;
                $table->dropForeign($constraintName);
            }
        });
        
        // Depois, alterar a coluna para nullable
        DB::statement('ALTER TABLE utmify_integrations MODIFY COLUMN user_id BIGINT UNSIGNED NULL');
        
        // Recriar o índice (sem foreign key)
        Schema::table('utmify_integrations', function (Blueprint $table) {
            if (!Schema::hasColumn('utmify_integrations', 'user_id')) {
                $table->index('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Antes de tornar não-nullable, deletar integrações globais
        DB::table('utmify_integrations')
            ->whereNull('user_id')
            ->delete();
        
        // Alterar coluna para não-nullable
        DB::statement('ALTER TABLE utmify_integrations MODIFY COLUMN user_id BIGINT UNSIGNED NOT NULL');
        
        // Recriar foreign key constraint
        Schema::table('utmify_integrations', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
