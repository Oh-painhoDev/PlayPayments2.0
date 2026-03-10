@extends('layouts.admin')

@section('title', 'Detalhes do Saque')
@section('page-title', 'Detalhes do Saque')
@section('page-description', 'Informações completas sobre a solicitação de saque')

@section('content')
<div class="p-6">
    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-600 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-600 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Withdrawal Header -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Saque #{{ substr($withdrawal->withdrawal_id, 4, 8) }}</h2>
                    <div>
                        @php
                            $statusConfig = [
                                'pending' => ['label' => 'Pendente', 'class' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30'],
                                'processing' => ['label' => 'Processando', 'class' => 'bg-blue-500/20 text-blue-600 border-blue-500/30'],
                                'completed' => ['label' => 'Concluído', 'class' => 'bg-green-500/20 text-green-600 border-green-500/30'],
                                'cancelled' => ['label' => 'Cancelado', 'class' => 'bg-gray-500/20 text-gray-600 border-gray-500/30'],
                                'failed' => ['label' => 'Falhou', 'class' => 'bg-green-500/20 text-green-600 border-green-500/30'],
                            ];
                            $config = $statusConfig[$withdrawal->status] ?? ['label' => ucfirst($withdrawal->status), 'class' => 'bg-gray-500/20 text-gray-600 border-gray-500/30'];
                        @endphp
                        <span class="px-3 py-1 text-sm font-medium rounded-full border {{ $config['class'] }}">
                            {{ $config['label'] }}
                        </span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">ID do Saque:</span>
                        <span class="text-gray-900 ml-2 font-mono">{{ $withdrawal->withdrawal_id }}</span>
                    </div>
                    
                    <div>
                        <span class="text-gray-600">Data da Solicitação:</span>
                        <span class="text-gray-900 ml-2">{{ $withdrawal->formatted_created_at }}</span>
                    </div>
                    
                    @if($withdrawal->completed_at)
                    <div>
                        <span class="text-gray-600">Data de Conclusão:</span>
                        <span class="text-gray-900 ml-2">{{ $withdrawal->formatted_completed_at }}</span>
                    </div>
                    @endif
                    
                    @if($withdrawal->external_id)
                    <div>
                        <span class="text-gray-600">ID Externo:</span>
                        <span class="text-gray-900 ml-2 font-mono">{{ $withdrawal->external_id }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Withdrawal Details -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalhes do Saque</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Valor Solicitado:</span>
                        <span class="text-gray-900 font-medium">{{ $withdrawal->formatted_amount }}</span>
                    </div>
                    
                    @php
                        $user = $withdrawal->user;
                        $feeCalculation = $user->calculateWithdrawalFee($withdrawal->amount);
                        $calculatedFee = $feeCalculation['fee'];
                        $totalToDebit = $feeCalculation['total_to_debit'];
                        $calculatedNetAmount = $feeCalculation['net_amount'];
                        $availableBalance = $user->wallet ? $user->wallet->available_balance : 0;
                        
                        // Se o valor + taxa exceder o saldo, calcular o valor máximo
                        if ($totalToDebit > $availableBalance && $withdrawal->status === 'pending') {
                            if ($user->withdrawal_fee_type === 'fixed' || $user->withdrawal_fee_type === 'global') {
                                $calculatedNetAmount = max(0, $availableBalance - $calculatedFee);
                            } else if ($user->withdrawal_fee_type === 'percentage') {
                                $calculatedNetAmount = $availableBalance / (1 + ($user->withdrawal_fee_percentage / 100));
                            } else if ($user->withdrawal_fee_type === 'both') {
                                $calculatedNetAmount = ($availableBalance - $user->withdrawal_fee_fixed) / (1 + ($user->withdrawal_fee_percentage / 100));
                            }
                            $feeCalculation = $user->calculateWithdrawalFee($calculatedNetAmount);
                            $calculatedFee = $feeCalculation['fee'];
                            $totalToDebit = $feeCalculation['total_to_debit'];
                        }
                    @endphp
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Taxa Estimada:</span>
                        <span class="text-orange-600">R$ {{ number_format($calculatedFee, 2, ',', '.') }}</span>
                    </div>
                    
                    @if($withdrawal->status === 'pending')
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                            <p class="text-sm text-blue-800 font-medium mb-2">💰 Cálculo na Aprovação:</p>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-blue-700">Valor que vai cair na conta:</span>
                                    <span class="text-blue-900 font-bold">R$ {{ number_format($calculatedNetAmount, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-blue-700">Taxa a ser cobrada:</span>
                                    <span class="text-blue-900">R$ {{ number_format($calculatedFee, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between border-t border-blue-300 pt-2">
                                    <span class="text-blue-700 font-medium">Total a debitar da carteira:</span>
                                    <span class="text-blue-900 font-bold">R$ {{ number_format($totalToDebit, 2, ',', '.') }}</span>
                                </div>
                                @if($totalToDebit > $availableBalance)
                                    <div class="mt-2 text-xs text-orange-700 bg-orange-50 p-2 rounded">
                                        ⚠️ O valor será ajustado automaticamente para o máximo disponível (R$ {{ number_format($availableBalance, 2, ',', '.') }})
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="flex justify-between">
                            <span class="text-gray-600">Taxa Cobrada:</span>
                            <span class="text-green-600">{{ $withdrawal->formatted_fee }}</span>
                        </div>
                        
                        <div class="flex justify-between border-t border-gray-200 pt-3">
                            <span class="text-gray-600">Valor que caiu na conta:</span>
                            <span class="text-green-600 font-medium">{{ $withdrawal->formatted_net_amount }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- PIX Information -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações da Chave PIX</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tipo de Chave:</span>
                        <span class="text-gray-900">{{ $withdrawal->pix_type_label }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Chave PIX:</span>
                        <span class="text-gray-900 font-mono">{{ $withdrawal->pix_key }}</span>
                    </div>
                </div>
            </div>

            <!-- User Information -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações do Usuário</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nome:</span>
                        <span class="text-gray-900">{{ $withdrawal->user->name }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">E-mail:</span>
                        <span class="text-gray-900">{{ $withdrawal->user->email }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Documento:</span>
                        <span class="text-gray-900">{{ $withdrawal->user->formatted_document }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tipo de Conta:</span>
                        <span class="text-gray-900">{{ $withdrawal->user->isPessoaFisica() ? 'Pessoa Física' : 'Pessoa Jurídica' }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tipo de Saque:</span>
                        <span class="px-2 py-1 text-xs rounded-full {{ $withdrawal->user->withdrawal_type === 'automatic' ? 'bg-green-500/10 text-green-600 border-green-500/20' : 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20' }} border">
                            {{ $withdrawal->user->withdrawal_type === 'automatic' ? 'Automático' : 'Manual' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Response Data -->
            @if($withdrawal->response_data)
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Dados da Resposta</h3>
                
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-300">
                    <pre class="text-xs text-gray-700 font-mono overflow-x-auto">{{ json_encode($withdrawal->response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
            @endif

            <!-- Webhook Data -->
            @if($withdrawal->webhook_data)
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Dados do Webhook</h3>
                
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-300">
                    <pre class="text-xs text-gray-700 font-mono overflow-x-auto">{{ json_encode($withdrawal->webhook_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status do Saque</h3>
                
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center
                        @if($withdrawal->isCompleted())
                            bg-green-500/10 text-green-500
                        @elseif($withdrawal->isProcessing())
                            bg-blue-500/10 text-blue-500
                        @elseif($withdrawal->isPending())
                            bg-yellow-500/10 text-yellow-500
                        @elseif($withdrawal->isFailed())
                            bg-green-500/10 text-green-500
                        @else
                            bg-gray-500/10 text-gray-500
                        @endif
                    ">
                        @if($withdrawal->isCompleted())
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        @elseif($withdrawal->isProcessing())
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif($withdrawal->isPending())
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif($withdrawal->isFailed())
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        @else
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        @endif
                    </div>
                </div>
                
                <div class="text-center">
                    <p class="text-lg font-semibold 
                        @if($withdrawal->isCompleted())
                            text-green-600
                        @elseif($withdrawal->isProcessing())
                            text-blue-600
                        @elseif($withdrawal->isPending())
                            text-yellow-400
                        @elseif($withdrawal->isFailed())
                            text-green-600
                        @else
                            text-gray-600
                        @endif
                    ">
                        {{ $withdrawal->status_label }}
                    </p>
                    
                    <p class="text-gray-600 text-sm mt-2">
                        @if($withdrawal->isCompleted())
                            O saque foi processado com sucesso e o valor foi enviado para a conta do usuário.
                        @elseif($withdrawal->isProcessing())
                            O saque está sendo processado pelo gateway de pagamento.
                        @elseif($withdrawal->isPending())
                            O saque está aguardando aprovação manual.
                        @elseif($withdrawal->isFailed())
                            O saque falhou. Verifique os detalhes do erro.
                        @else
                            O saque foi cancelado.
                        @endif
                    </p>
                    
                    @if($withdrawal->error_message)
                    <div class="mt-3 p-3 bg-green-500/10 border border-green-500/20 rounded-lg">
                        <p class="text-green-600 text-sm">{{ $withdrawal->error_message }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações</h3>
                
                <div class="space-y-3">
                    <a href="{{ route('admin.withdrawals.index') }}" class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm text-center transition-colors">
                        Voltar para Lista
                    </a>
                    
                    @if($withdrawal->status === 'pending' && $withdrawal->user->withdrawal_type === 'manual')
                        <button 
                            onclick="openApprovalModal()"
                            class="w-full bg-green-600 hover:bg-green-700 text-gray-900 px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            Aprovar Saque
                        </button>
                        
                        <button 
                            onclick="openRejectionModal()"
                            class="w-full bg-green-600 hover:bg-green-700 text-gray-900 px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            Rejeitar Saque
                        </button>
                    @endif
                    
                    @if($withdrawal->isProcessing())
                    <button 
                        onclick="refreshStatus()"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-gray-900 px-4 py-2 rounded-lg text-sm transition-colors"
                        id="refreshBtn"
                    >
                        Atualizar Status
                    </button>
                    @endif
                </div>
            </div>

            <!-- User Wallet -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Carteira do Usuário</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 text-sm">Saldo Atual:</span>
                        <span class="text-gray-900 font-medium">{{ $withdrawal->user->formatted_wallet_balance }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 text-sm">Total Recebido:</span>
                        <span class="text-gray-900 font-medium">R$ {{ number_format($withdrawal->user->wallet->total_received, 2, ',', '.') }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 text-sm">Total Sacado:</span>
                        <span class="text-gray-900 font-medium">R$ {{ number_format($withdrawal->user->wallet->total_withdrawn, 2, ',', '.') }}</span>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="{{ route('admin.users.show', $withdrawal->user) }}" class="text-blue-600 hover:text-blue-700 text-sm">
                        Ver perfil completo do usuário
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div id="approvalModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Aprovar Saque</h3>
                    <button onclick="closeApprovalModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <p class="text-gray-700 mb-4">
                    Tem certeza que deseja aprovar este saque? Esta ação irá processar o pagamento imediatamente.
                </p>

                <form id="approvalForm" action="{{ route('admin.withdrawals.approve', $withdrawal) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            onclick="closeApprovalModal()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-gray-900 px-6 py-2 rounded-lg text-sm transition-colors"
                        >
                            Confirmar Aprovação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectionModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Rejeitar Saque</h3>
                    <button onclick="closeRejectionModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="rejectionForm" action="{{ route('admin.withdrawals.reject', $withdrawal) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Motivo da Rejeição *
                        </label>
                        <textarea 
                            id="rejection_reason" 
                            name="rejection_reason" 
                            rows="3" 
                            required
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            placeholder="Informe o motivo da rejeição"
                        ></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            onclick="closeRejectionModal()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-gray-900 px-6 py-2 rounded-lg text-sm transition-colors"
                        >
                            Confirmar Rejeição
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openApprovalModal() {
    document.getElementById('approvalModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeApprovalModal() {
    document.getElementById('approvalModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

function openRejectionModal() {
    document.getElementById('rejectionModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeRejectionModal() {
    document.getElementById('rejectionModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

function refreshStatus() {
    const btn = document.getElementById('refreshBtn');
    
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `
            <svg class="w-4 h-4 inline mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Atualizando...
        `;
    }
    
    // Reload the page after a short delay
    setTimeout(() => {
        location.reload();
    }, 1000);
}
</script>
@endpush
@endsection