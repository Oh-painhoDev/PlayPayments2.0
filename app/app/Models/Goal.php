<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'target_value',
        'period',
        'start_date',
        'end_date',
        'is_active',
        'display_order',
        'description',
        'reward_type',
        'reward_value',
        'reward_description',
        'auto_reward',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'reward_value' => 'decimal:2',
        'auto_reward' => 'boolean',
    ];

    /**
     * Relacionamento com usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com conquistas
     */
    public function achievements(): HasMany
    {
        return $this->hasMany(UserGoalAchievement::class);
    }

    /**
     * Verificar se meta foi atingida e premiar automaticamente se configurado
     */
    public function checkAndReward($userId = null): ?UserGoalAchievement
    {
        $targetUserId = $userId ?? $this->user_id;
        
        if (!$targetUserId) {
            return null; // Precisamos de um usuário para premiar
        }
        
        // Se meta é pessoal (user_id não null), garantir que pertence ao usuário
        // Se meta é global (user_id null), qualquer usuário pode ser premiado
        if ($this->user_id && $this->user_id != $targetUserId) {
            return null; // Meta pessoal não pertence a este usuário
        }
        // Se user_id é null (meta global), qualquer usuário pode ser premiado quando atingir a meta

        // Verificar se já foi premiado neste período
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        
        $existingAchievement = \App\Models\UserGoalAchievement::where('user_id', $targetUserId)
            ->where('goal_id', $this->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('reward_given', true)
            ->first();

        if ($existingAchievement) {
            return null; // Já foi premiado neste período
        }

        // Verificar se meta foi atingida
        // Para metas globais, verificar baseado no progresso do usuário específico
        $currentValue = $targetUserId ? $this->getCurrentValueForUser($targetUserId) : $this->current_value;
        if ($currentValue < $this->target_value) {
            return null; // Meta não atingida ainda
        }

        // Meta atingida! Criar conquista
        $achievement = \App\Models\UserGoalAchievement::create([
            'user_id' => $targetUserId,
            'goal_id' => $this->id,
            'achieved_value' => $currentValue,
            'reward_value' => $this->reward_value,
            'reward_type' => $this->reward_type,
            'reward_description' => $this->reward_description,
            'reward_given' => $this->auto_reward,
            'reward_given_at' => $this->auto_reward ? now() : null,
        ]);

        // Se auto_reward está ativo, aplicar o prêmio
        if ($this->auto_reward && $this->reward_type === 'cash' && $this->reward_value) {
            $this->applyReward($targetUserId, $achievement);
        }

        return $achievement;
    }

    /**
     * Aplicar prêmio ao usuário
     */
    private function applyReward($userId, UserGoalAchievement $achievement)
    {
        $user = \App\Models\User::find($userId);
        if (!$user || !$user->wallet) {
            return;
        }

        // Adicionar ao saldo do usuário via wallet
        if ($this->reward_type === 'cash' && $this->reward_value) {
            try {
                $user->wallet->addCredit(
                    amount: $this->reward_value,
                    category: 'goal_reward',
                    description: "Prêmio por atingir meta: {$this->name}",
                    metadata: [
                        'goal_id' => $this->id,
                        'goal_name' => $this->name,
                        'achievement_id' => $achievement->id,
                    ],
                    referenceId: 'GOAL_REWARD_' . $this->id . '_' . $achievement->id
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Erro ao aplicar prêmio de meta', [
                    'user_id' => $userId,
                    'goal_id' => $this->id,
                    'reward_value' => $this->reward_value,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $achievement->update([
            'reward_given' => true,
            'reward_given_at' => now(),
        ]);
    }

    /**
     * Verificar se usuário já atingiu esta meta
     */
    public function hasUserAchieved($userId): bool
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        
        return \App\Models\UserGoalAchievement::where('user_id', $userId)
            ->where('goal_id', $this->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('reward_given', true)
            ->exists();
    }

    /**
     * Calcular valor atual da meta baseado no tipo
     * NOTA: Para metas globais, use getCurrentValueForUser($userId) para progresso individual
     * Este accessor funciona apenas para metas pessoais (user_id não null)
     */
    public function getCurrentValueAttribute(): float
    {
        // Para metas globais (user_id null), retornar 0
        // Use getCurrentValueForUser($userId) para calcular progresso individual
        if (!$this->user_id) {
            return 0;
        }
        
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        $query = Transaction::where('status', 'paid')
            ->where('is_retained', false)
            ->where('user_id', $this->user_id) // Meta pessoal: usar user_id da meta
            ->whereBetween('created_at', [$startDate, $endDate]);

        switch ($this->type) {
            case 'faturamento':
            case 'vendas':
                return (float) $query->sum('amount') ?? 0;

            case 'transacoes':
            case 'pedidos':
                return (float) $query->count();

            case 'clientes':
                // Para clientes, contar emails únicos
                return (float) $query
                    ->selectRaw('COUNT(DISTINCT JSON_UNQUOTE(JSON_EXTRACT(customer_data, "$.email"))) as unique_customers')
                    ->value('unique_customers') ?? 0;

            default:
                return 0;
        }
    }
    
    /**
     * Calcular valor atual da meta para um usuário específico
     * Este método permite calcular o progresso individual mesmo para metas globais
     */
    public function getCurrentValueForUser($userId): float
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        $query = Transaction::where('status', 'paid')
            ->where('is_retained', false)
            ->where('user_id', $userId) // Sempre filtrar por usuário para progresso individual
            ->whereBetween('created_at', [$startDate, $endDate]);

        switch ($this->type) {
            case 'faturamento':
            case 'vendas':
                return (float) $query->sum('amount') ?? 0;

            case 'transacoes':
            case 'pedidos':
                return (float) $query->count();

            case 'clientes':
                // Para clientes, contar emails únicos
                return (float) $query
                    ->selectRaw('COUNT(DISTINCT JSON_UNQUOTE(JSON_EXTRACT(customer_data, "$.email"))) as unique_customers')
                    ->value('unique_customers') ?? 0;

            default:
                return 0;
        }
    }

    /**
     * Calcular porcentagem da meta
     * NOTA: Para metas globais, use getPercentageForUser($userId) para progresso individual
     * Este accessor funciona apenas para metas pessoais (user_id não null)
     */
    public function getPercentageAttribute(): float
    {
        if ($this->target_value <= 0) {
            return 0;
        }

        $current = $this->current_value;
        $percentage = ($current / $this->target_value) * 100;
        
        return min(100, max(0, round($percentage, 2)));
    }
    
    /**
     * Calcular porcentagem da meta para um usuário específico
     */
    public function getPercentageForUser($userId): float
    {
        if ($this->target_value <= 0) {
            return 0;
        }

        $current = $this->getCurrentValueForUser($userId);
        $percentage = ($current / $this->target_value) * 100;
        
        return min(100, max(0, round($percentage, 2)));
    }

    /**
     * Obter data de início baseado no período
     */
    private function getStartDate(): Carbon
    {
        if ($this->period === 'custom' && $this->start_date) {
            return Carbon::parse($this->start_date)->startOfDay();
        }

        switch ($this->period) {
            case 'monthly':
                return Carbon::now()->startOfMonth();
            case 'yearly':
                return Carbon::now()->startOfYear();
            default:
                return Carbon::now()->startOfMonth();
        }
    }

    /**
     * Obter data de fim baseado no período
     */
    private function getEndDate(): Carbon
    {
        if ($this->period === 'custom' && $this->end_date) {
            return Carbon::parse($this->end_date)->endOfDay();
        }

        switch ($this->period) {
            case 'monthly':
                return Carbon::now()->endOfMonth();
            case 'yearly':
                return Carbon::now()->endOfYear();
            default:
                return Carbon::now()->endOfMonth();
        }
    }

    /**
     * Scope para metas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para metas do usuário (inclui metas pessoais e globais)
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereNull('user_id'); // Metas globais (user_id null) são visíveis para todos
        });
    }

    /**
     * Scope para ordenar por display_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('created_at', 'desc');
    }
}
