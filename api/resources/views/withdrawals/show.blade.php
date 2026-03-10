@extends('layouts.dashboard')

@section('title', 'Detalhes do Saque')
@section('page-title', 'Detalhes do Saque')
@section('page-description', 'Informações completas sobre sua solicitação de saque')

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
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Taxa de Saque:</span>
                        <span class="text-green-600">{{ $withdrawal->formatted_fee }}</span>
                    </div>
                    
                    <div class="flex justify-between border-t border-gray-200 pt-3">
                        <span class="text-gray-600">Valor Líquido:</span>
                        <span class="text-green-600 font-medium">{{ $withdrawal->formatted_net_amount }}</span>
                    </div>
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

            <!-- Status History -->
            @if($withdrawal->webhook_data)
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Histórico de Status</h3>
                
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 mr-3"></div>
                        <div>
                            <p class="text-gray-900 text-sm">Saque solicitado</p>
                            <p class="text-gray-600 text-xs">{{ $withdrawal->formatted_created_at }}</p>
                        </div>
                    </div>
                    
                    @if($withdrawal->isProcessing() || $withdrawal->isCompleted() || $withdrawal->isFailed() || $withdrawal->isCancelled())
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-yellow-500 rounded-full mt-1.5 mr-3"></div>
                        <div>
                            <p class="text-gray-900 text-sm">Saque em processamento</p>
                            <p class="text-gray-600 text-xs">{{ $withdrawal->created_at->addMinutes(1)->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($withdrawal->isCompleted())
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-green-500 rounded-full mt-1.5 mr-3"></div>
                        <div>
                            <p class="text-gray-900 text-sm">Saque concluído</p>
                            <p class="text-gray-600 text-xs">{{ $withdrawal->formatted_completed_at }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($withdrawal->isFailed())
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-green-500 rounded-full mt-1.5 mr-3"></div>
                        <div>
                            <p class="text-gray-900 text-sm">Saque falhou</p>
                            <p class="text-gray-600 text-xs">{{ $withdrawal->updated_at->format('d/m/Y H:i') }}</p>
                            @if($withdrawal->error_message)
                            <p class="text-green-600 text-xs mt-1">{{ $withdrawal->error_message }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    @if($withdrawal->isCancelled())
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-gray-500 rounded-full mt-1.5 mr-3"></div>
                        <div>
                            <p class="text-gray-900 text-sm">Saque cancelado</p>
                            <p class="text-gray-600 text-xs">{{ $withdrawal->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
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
                            Seu saque foi processado com sucesso e o valor foi enviado para sua conta.
                        @elseif($withdrawal->isProcessing())
                            Seu saque está sendo processado. Isso pode levar até 24 horas úteis.
                        @elseif($withdrawal->isPending())
                            Seu saque está pendente de aprovação. Aguarde a análise.
                        @elseif($withdrawal->isFailed())
                            Seu saque falhou. Por favor, verifique os detalhes ou entre em contato com o suporte.
                        @else
                            Seu saque foi cancelado.
                        @endif
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações</h3>
                
                <div class="space-y-3">
                    <a href="{{ route('wallet.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-gray-900 px-4 py-2 rounded-lg text-sm text-center transition-colors">
                        Voltar para Saques
                    </a>
                    
                    @if($withdrawal->isProcessing() || $withdrawal->isPending())
                    <button 
                        onclick="refreshStatus()"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-gray-900 px-4 py-2 rounded-lg text-sm transition-colors"
                        id="refreshBtn"
                    >
                        Atualizar Status
                    </button>
                    @endif
                    
                    @if($withdrawal->isFailed())
                    <a href="{{ route('wallet.create') }}" class="block w-full bg-green-600 hover:bg-green-700 text-gray-900 px-4 py-2 rounded-lg text-sm text-center transition-colors">
                        Tentar Novamente
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
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

// Auto-refresh for processing withdrawals
@if($withdrawal->isProcessing())
    setInterval(() => {
        location.reload();
    }, 30000); // Refresh every 30 seconds
@endif
</script>
@endpush
@endsection