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
        if (!Schema::hasTable('goals')) {
            return;
        }
        
        // Atualizar display_order das metas existentes para começar do 1
        // Agrupar por user_id (incluindo null para globais) e ordenar por created_at
        $goals = DB::table('goals')
            ->orderByRaw('user_id IS NULL, user_id')
            ->orderBy('created_at')
            ->get();
        
        $orderByUser = [];
        
        foreach ($goals as $goal) {
            // Usar 'null' como string para agrupar metas globais
            $userId = $goal->user_id ?? 'null';
            
            if (!isset($orderByUser[$userId])) {
                $orderByUser[$userId] = 1;
            }
            
            DB::table('goals')
                ->where('id', $goal->id)
                ->update(['display_order' => $orderByUser[$userId]]);
            
            $orderByUser[$userId]++;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('goals')) {
            return;
        }
        
        // Reverter display_order para 0
        DB::table('goals')->update(['display_order' => 0]);
    }
};
