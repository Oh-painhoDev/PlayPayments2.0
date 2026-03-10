@extends('layouts.admin')

@section('title', 'Detalhes da Venda Retida')
@section('page-title', 'Detalhes da Venda Retida')
@section('page-description', 'Informações completas sobre a transação retida')

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
            <!-- Transaction Header -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Transação #{{ substr($transaction->transaction_id, 0, 12) }}</h2>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="px-3 py-1 text-sm font-medium rounded-full border bg-green-500/20 text-green-600 border-green-500/30">
                                Pago (Admin)
                            </span>
                            <span class="px-3 py-1 text-sm font-medium rounded-full border bg-yellow-500/20 text-yellow-400 border-yellow-500/30">
                                Pendente (Usuário)
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">ID da Transação:</span>
                        <span class="text-gray-900 ml-2 font-mono">{{ $transaction->transaction_id }}</span>
                    </div>
                    
                    @if($transaction->external_id)
                    <div>
                        <span class="text-gray-600">ID Externo:</span>
                        <span class="text-gray-900 ml-2 font-mono">{{ $transaction->external_id }}</span>
                    </div>
                    @endif
                    
                    <div>
                        <span class="text-gray-600">Criado em:</span>
                        <span class="text-gray-900 ml-2">{{ $transaction->created_at->format('d/m/Y \à\s H:i') }}</span>
                    </div>
                    
                    <div>
                        <span class="text-gray-600">Retido em:</span>
                        <span class="text-gray-900 ml-2">{{ $transaction->retention_date ? $transaction->retention_date->format('d/m/Y \à\s H:i') : 'N/A' }}</span>
                    </div>
                    
                    @if($transaction->paid_at)
                    <div>
                        <span class="text-gray-600">Pago em:</span>
                        <span class="text-gray-900 ml-2">{{ $transaction->paid_at->format('d/m/Y \à\s H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Financial Details -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalhes Financeiros</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Valor Bruto:</span>
                        <span class="text-gray-900 font-medium">{{ $transaction->formatted_amount }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Taxa:</span>
                        <span class="text-green-600">{{ $transaction->formatted_fee_amount }}</span>
                    </div>
                    
                    <div class="flex justify-between border-t border-gray-200 pt-3">
                        <span class="text-gray-600">Valor Líquido:</span>
                        <span class="text-green-600 font-medium">{{ $transaction->formatted_net_amount }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalhes do Pagamento</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Método de Pagamento:</span>
                        <span class="text-gray-900">{{ strtoupper(str_replace('_', ' ', $transaction->payment_method)) }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Gateway:</span>
                        <span class="text-gray-900">{{ $transaction->gateway ? $transaction->gateway->name : 'N/A' }}</span>
                    </div>
                    
                    @if($transaction->payment_method === 'credit_card' && isset($transaction->payment_data['installments']))
                    <div class="flex justify-between">
                        <span class="text-gray-600">Parcelas:</span>
                        <span class="text-gray-900">{{ $transaction->payment_data['installments'] }}x</span>
                    </div>
                    @endif
                    
                    @if($transaction->expires_at)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Expira em:</span>
                        <span class="text-gray-900">{{ $transaction->expires_at->format('d/m/Y \à\s H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Customer Information -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações do Cliente</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nome:</span>
                        <span class="text-gray-900">{{ $transaction->customer_data['name'] ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">E-mail:</span>
                        <span class="text-gray-900">{{ $transaction->customer_data['email'] ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Documento:</span>
                        <span class="text-gray-900">{{ $transaction->customer_data['document'] ?? 'N/A' }}</span>
                    </div>
                    
                    @if(isset($transaction->customer_data['phone']) && $transaction->customer_data['phone'])
                    <div class="flex justify-between">
                        <span class="text-gray-600">Telefone:</span>
                        <span class="text-gray-900">{{ $transaction->customer_data['phone'] }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Metadata -->
            @if($transaction->metadata && count((array)$transaction->metadata) > 0)
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Metadados</h3>
                
                <div class="space-y-4">
                    @foreach((array)$transaction->metadata as $key => $value)
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                        <span class="text-gray-900">{{ is_array($value) || is_object($value) ? json_encode($value) : $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- User Information -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações do Usuário</h3>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-600">Nome:</span>
                        <p class="text-gray-900 font-medium">{{ $transaction->user->name }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Email:</span>
                        <p class="text-gray-900 font-medium">{{ $transaction->user->email }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Documento:</span>
                        <p class="text-gray-900 font-medium">{{ $transaction->user->formatted_document }}</p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="{{ route('admin.users.show', $transaction->user) }}" class="text-blue-600 hover:text-blue-700 text-sm">
                        Ver perfil completo
                    </a>
                </div>
            </div>

            <!-- Retention Information -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações da Retenção</h3>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-600">Status:</span>
                        <div class="flex items-center mt-1">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-green-600 text-sm">Retido</span>
                        </div>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Data da Retenção:</span>
                        <p class="text-gray-900 font-medium">{{ $transaction->retention_date ? $transaction->retention_date->format('d/m/Y H:i') : 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Valor Retido:</span>
                        <p class="text-gray-900 font-medium">{{ $transaction->formatted_amount }}</p>
                    </div>
                </div>
                
                <div class="mt-4 p-3 bg-blue-500/10 border border-blue-500/20 rounded-lg">
                    <p class="text-blue-700 text-xs">
                        <strong>Nota:</strong> Esta transação foi retida pelo sistema e não foi creditada na carteira do usuário.
                    </p>
                </div>
            </div>

            <!-- Status Information -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status da Transação</h3>
                
                <div class="space-y-4">
                    <div>
                        <span class="text-sm text-gray-600">Status para Admin:</span>
                        <div class="flex items-center mt-1">
                            <div class="flex flex-wrap gap-1">
                                <span class="px-2 py-1 text-xs rounded bg-green-500/20 text-green-600 border-green-500/30">
                                    Pago
                                </span>
                                @if($transaction->status === 'refunded' || $transaction->status === 'partially_refunded' || $transaction->status === 'chargeback')
                                <span class="px-2 py-1 text-xs rounded bg-green-500/20 text-green-600 border-green-500/30">
                                    {{ $transaction->status === 'refunded' ? 'Reembolsado' : ($transaction->status === 'partially_refunded' ? 'Reemb. Parcial' : 'Chargeback') }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Status para Usuário:</span>
                        <div class="flex items-center mt-1">
                            <span class="px-2 py-1 text-xs rounded bg-yellow-500/20 text-yellow-400 border-yellow-500/30">
                                Pendente
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                        <p class="text-yellow-300 text-xs">
                            <strong>Importante:</strong> O usuário vê esta transação como "Pendente" e não recebe notificações de pagamento.
                            @if($transaction->status === 'refunded' || $transaction->status === 'partially_refunded' || $transaction->status === 'chargeback')
                            <br><br><strong>Nota:</strong> Esta transação foi reembolsada, mas continua aparecendo como "Pendente" para o usuário.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações</h3>
                
                <div class="space-y-3">
                    <a href="{{ route('admin.setup.retained-sales') }}" class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm text-center transition-colors">
                        Voltar para Lista
                    </a>

                    @if(!($transaction->status === 'refunded' || $transaction->status === 'partially_refunded' || $transaction->status === 'chargeback'))
                        <form action="{{ route('admin.setup.retained-sales.return', $transaction->transaction_id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja devolver esta venda para o usuário? Isso irá liberar o valor para a carteira do usuário e enviar uma notificação de pagamento.');">
                            @csrf
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-gray-900 px-4 py-2 rounded-lg text-sm transition-colors">
                                Devolver para o Usuário
                            </button>
                        </form>
                    @else
                        <div class="w-full bg-gray-600 text-gray-900 px-4 py-2 rounded-lg text-sm text-center opacity-50 cursor-not-allowed">
                            Transação Reembolsada
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection