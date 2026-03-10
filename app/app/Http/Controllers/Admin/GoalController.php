<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Mostrar todas as metas (pessoais e globais)
        $query = Goal::with('user')->ordered();

        // Filtro por usuário
        if ($request->has('user_id') && $request->user_id) {
            if ($request->user_id === 'global') {
                // Mostrar apenas metas globais
                $query->whereNull('user_id');
            } else {
                // Mostrar apenas metas do usuário específico
                $query->where('user_id', $request->user_id);
            }
        }

        // Filtro por tipo
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filtro por status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        } else {
            // Por padrão, mostrar apenas metas ativas
            $query->where('is_active', true);
        }

        $goals = $query->paginate(20);
        $users = User::where('role', 'user')->orderBy('name')->get(); // Apenas usuários regulares
        $types = ['faturamento', 'vendas', 'transacoes', 'pedidos', 'clientes'];

        return view('admin.goals.index', compact('goals', 'users', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Apenas usuários regulares (não admin) podem ter metas
        $users = User::where('role', 'user')->orderBy('name')->get();
        $types = ['faturamento', 'vendas', 'transacoes', 'pedidos', 'clientes'];
        
        return view('admin.goals.create', compact('users', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:faturamento,vendas,transacoes,pedidos,clientes',
            'target_value' => 'required|numeric|min:0',
            'period' => 'required|string|in:monthly,yearly,custom',
            'start_date' => 'nullable|date|required_if:period,custom',
            'end_date' => 'nullable|date|after_or_equal:start_date|required_if:period,custom',
            'is_active' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);
        
        // Converter is_active para boolean
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->input('is_active') : true;
        
        // Converter auto_reward para boolean
        $validated['auto_reward'] = $request->has('auto_reward') ? (bool)$request->input('auto_reward') : false;
        
        // Se user_id for vazio ou "global", definir como null (meta global)
        $userId = $request->input('user_id');
        if (empty($userId) || $userId === 'global' || $userId === '') {
            $validated['user_id'] = null;
        } else {
            // Validar se o usuário existe
            if (!User::where('id', $userId)->exists()) {
                return back()->withInput()
                    ->withErrors(['user_id' => 'Usuário não encontrado.']);
            }
            $validated['user_id'] = $userId;
        }
        
        // Se display_order não foi fornecido ou é 0, usar o próximo número disponível
        if (empty($validated['display_order']) || $validated['display_order'] == 0) {
            // Buscar o maior display_order para o mesmo user_id (pessoal ou global)
            $maxOrder = Goal::where('user_id', $validated['user_id'])
                ->where('is_active', true)
                ->max('display_order') ?? 0;
            $validated['display_order'] = $maxOrder + 1;
        }

        try {
            Goal::create($validated);

            return redirect()->route('admin.goals.index')
                ->with('success', 'Meta criada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar meta: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Erro ao criar meta: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Goal $goal)
    {
        $goal->load('user');
        return view('admin.goals.show', compact('goal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Goal $goal)
    {
        // Apenas usuários regulares (não admin) podem ter metas
        $users = User::where('role', 'user')->orderBy('name')->get();
        $types = ['faturamento', 'vendas', 'transacoes', 'pedidos', 'clientes'];
        
        return view('admin.goals.edit', compact('goal', 'users', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Goal $goal)
    {
        $validated = $request->validate([
            'user_id' => 'nullable',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:faturamento,vendas,transacoes,pedidos,clientes',
            'target_value' => 'required|numeric|min:0',
            'period' => 'required|string|in:monthly,yearly,custom',
            'start_date' => 'nullable|date|required_if:period,custom',
            'end_date' => 'nullable|date|after_or_equal:start_date|required_if:period,custom',
            'is_active' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'reward_type' => 'nullable|string|in:cash,bonus,discount,custom',
            'reward_value' => 'nullable|numeric|min:0',
            'reward_description' => 'nullable|string',
            'auto_reward' => 'nullable|boolean',
        ]);
        
        // Converter is_active para boolean
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->input('is_active') : $goal->is_active;
        
        // Converter auto_reward para boolean
        $validated['auto_reward'] = $request->has('auto_reward') ? (bool)$request->input('auto_reward') : $goal->auto_reward;
        
        // Se user_id for vazio ou "global", definir como null (meta global)
        $userId = $request->input('user_id');
        if (empty($userId) || $userId === 'global' || $userId === '') {
            $validated['user_id'] = null;
        } else {
            // Validar se o usuário existe
            if (!User::where('id', $userId)->exists()) {
                return back()->withInput()
                    ->withErrors(['user_id' => 'Usuário não encontrado.']);
            }
            $validated['user_id'] = $userId;
        }
        
        // Se display_order não foi fornecido ou é 0, usar o próximo número disponível
        if (empty($validated['display_order']) || $validated['display_order'] == 0) {
            // Buscar o maior display_order para o mesmo user_id (pessoal ou global)
            $maxOrder = Goal::where('user_id', $validated['user_id'])
                ->where('id', '!=', $goal->id)
                ->where('is_active', true)
                ->max('display_order') ?? 0;
            $validated['display_order'] = $maxOrder + 1;
        }

        try {
            $goal->update($validated);

            return redirect()->route('admin.goals.index')
                ->with('success', 'Meta atualizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar meta: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Erro ao atualizar meta: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Goal $goal)
    {
        try {
            $goal->delete();

            return redirect()->route('admin.goals.index')
                ->with('success', 'Meta excluída com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir meta: ' . $e->getMessage());
            
            return back()->with('error', 'Erro ao excluir meta: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(Goal $goal)
    {
        try {
            $goal->is_active = !$goal->is_active;
            $goal->save();

            return response()->json([
                'success' => true,
                'is_active' => $goal->is_active,
                'message' => $goal->is_active ? 'Meta ativada' : 'Meta desativada'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao alterar status da meta: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar status'
            ], 500);
        }
    }

    /**
     * Manually reward user for achieving a goal
     */
    public function rewardUser(Request $request, Goal $goal)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);
            
            $targetUserId = $request->input('user_id');
            
            // Se meta é pessoal (user_id não null), garantir que o usuário premiado seja o dono da meta
            if ($goal->user_id && $goal->user_id != $targetUserId) {
                return back()->with('error', 'Esta meta é pessoal e só pode premiar o usuário ' . ($goal->user->name ?? 'dono da meta') . '.');
            }

            $user = User::find($targetUserId);
            if (!$user) {
                return back()->with('error', 'Usuário não encontrado.');
            }

            // Verificar se meta foi atingida
            // Para metas globais e pessoais, verificar progresso do usuário específico
            $currentValue = $goal->getCurrentValueForUser($targetUserId);
            $percentage = $goal->getPercentageForUser($targetUserId);
            
            if ($currentValue < $goal->target_value) {
                return back()->with('error', 'Meta ainda não foi atingida pelo usuário. Progresso atual: ' . number_format($percentage, 2) . '%.');
            }

            // Verificar se já foi premiado
            if ($goal->hasUserAchieved($targetUserId)) {
                return back()->with('error', 'Usuário já foi premiado por esta meta neste período.');
            }

            // Aplicar prêmio manualmente
            $achievement = $goal->checkAndReward($targetUserId);
            
            if (!$achievement) {
                return back()->with('error', 'Erro ao criar conquista.');
            }

            // Se não for auto_reward, aplicar manualmente
            if (!$goal->auto_reward && $goal->reward_type === 'cash' && $goal->reward_value) {
                // Aplicar prêmio diretamente
                if (!$user->wallet) {
                    return back()->with('error', 'Usuário não possui wallet.');
                }

                try {
                    $user->wallet->addCredit(
                        amount: $goal->reward_value,
                        category: 'goal_reward',
                        description: "Prêmio por atingir meta: {$goal->name}",
                        metadata: [
                            'goal_id' => $goal->id,
                            'goal_name' => $goal->name,
                            'achievement_id' => $achievement->id,
                        ],
                        referenceId: 'GOAL_REWARD_' . $goal->id . '_' . $achievement->id
                    );

                    $achievement->update([
                        'reward_given' => true,
                        'reward_given_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erro ao aplicar prêmio manual de meta', [
                        'user_id' => $user->id,
                        'goal_id' => $goal->id,
                        'error' => $e->getMessage()
                    ]);
                    
                    return back()->with('error', 'Erro ao creditar prêmio na wallet: ' . $e->getMessage());
                }
            }

            return back()->with('success', 'Prêmio aplicado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao premiar usuário: ' . $e->getMessage());
            
            return back()->with('error', 'Erro ao premiar usuário: ' . $e->getMessage());
        }
    }
}
