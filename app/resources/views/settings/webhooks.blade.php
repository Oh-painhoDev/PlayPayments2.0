@extends('layouts.dashboard')

@section('title', 'Configurações - Webhooks')
@section('page-title', 'Webhooks')
@section('page-description', 'Configure webhooks para receber notificações de transações')

@section('content')
<div class="p-6 space-y-6">
    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 px-6 py-4 rounded-r-lg flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 px-6 py-4 rounded-r-lg flex items-start">
            <svg class="w-5 h-5 text-rose-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 px-6 py-4 rounded-r-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-rose-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <h4 class="font-semibold mb-2">Erros encontrados:</h4>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 via-emerald-500 to-teal-500 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Gerenciar Webhooks</h1>
                <p class="text-green-100">Receba notificações em tempo real sobre suas transações</p>
            </div>
            <div class="flex space-x-3">
                <button 
                    onclick="openDispatchModal()"
                    class="bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white px-5 py-3 rounded-xl font-medium transition-all shadow-lg hover:shadow-xl border border-white/30 flex items-center"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Disparar Teste
                </button>
                <button 
                    onclick="openAddWebhookModal()"
                    class="bg-white hover:bg-gray-100 text-green-700 px-6 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl flex items-center"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Novo Webhook
                </button>
            </div>
        </div>
    </div>

    <!-- Webhooks Grid -->
    @if($webhooks->isEmpty())
        <div class="bg-white rounded-2xl border-2 border-dashed border-gray-300 p-16 text-center">
            <div class="w-24 h-24 bg-gradient-to-br from-green-100 to-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Nenhum webhook configurado</h3>
            <p class="text-gray-500 mb-6">Comece criando seu primeiro webhook para receber notificações</p>
            <button 
                onclick="openAddWebhookModal()"
                class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-8 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl inline-flex items-center"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Criar Primeiro Webhook
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6">
            @foreach($webhooks as $webhook)
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden hover:shadow-2xl transition-all duration-300 group">
                    <!-- Webhook Header com Gradient -->
                    <div class="bg-gradient-to-r from-{{ $webhook->is_active ? 'emerald' : 'gray' }}-500 to-{{ $webhook->is_active ? 'teal' : 'slate' }}-500 p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4 flex-1">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-white text-xl font-bold">{{ $webhook->description ?: 'Webhook #' . $webhook->id }}</h3>
                                    <div class="flex items-center mt-2 bg-white/10 backdrop-blur-sm rounded-lg px-3 py-1.5 w-fit">
                                        <code class="text-white/90 text-sm font-mono">{{ Str::limit($webhook->url, 60) }}</code>
                                        <button 
                                            onclick="copyToClipboard('{{ $webhook->url }}', 'URL copiada!')"
                                            class="ml-3 text-white/80 hover:text-white transition-colors"
                                            title="Copiar URL"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button 
                                    onclick="openEditWebhookModal({{ $webhook->id }}, '{{ $webhook->url }}', '{{ $webhook->description }}', {{ json_encode($webhook->events) }}, {{ $webhook->is_active ? 'true' : 'false' }})"
                                    class="bg-white/20 backdrop-blur-sm hover:bg-white/30 p-3 rounded-xl transition-all"
                                    title="Editar"
                                >
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button 
                                    onclick="confirmDeleteWebhook({{ $webhook->id }})"
                                    class="bg-rose-500/20 backdrop-blur-sm hover:bg-rose-500/30 p-3 rounded-xl transition-all"
                                    title="Excluir"
                                >
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Webhook Body -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <!-- Secret Key Card -->
                            <div class="bg-gradient-to-br from-amber-50 to-orange-50 border-2 border-amber-200 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-amber-700 uppercase tracking-wide">Secret Key</span>
                                    <button 
                                        onclick="confirmRegenerateSecret({{ $webhook->id }})"
                                        class="text-amber-600 hover:text-amber-700 transition-colors"
                                        title="Regenerar Secret"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between">
                                    <code class="text-sm text-gray-900 font-mono font-bold">{{ $webhook->masked_secret }}</code>
                                    <button 
                                        onclick="copyToClipboard('{{ $webhook->secret }}', 'Secret copiado!')"
                                        class="ml-2 text-amber-600 hover:text-amber-700 transition-colors"
                                        title="Copiar Secret"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Events Card -->
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-4">
                                <span class="text-xs font-semibold text-blue-700 uppercase tracking-wide block mb-2">Eventos Ativos</span>
                                <p class="text-sm text-gray-900 font-semibold">{{ $webhook->formatted_events }}</p>
                            </div>
                            
                            <!-- Last Trigger Card -->
                            <div class="bg-gradient-to-br from-purple-50 to-pink-50 border-2 border-purple-200 rounded-xl p-4">
                                <span class="text-xs font-semibold text-purple-700 uppercase tracking-wide block mb-2">Último Disparo</span>
                                <p class="text-sm text-gray-900 font-semibold">{{ $webhook->last_triggered_at ? $webhook->last_triggered_at->format('d/m/Y H:i') : 'Nunca disparado' }}</p>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <div class="flex items-center space-x-2">
                                @if($webhook->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-300">
                                        <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2 animate-pulse"></span>
                                        Ativo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-300">
                                        <span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span>
                                        Inativo
                                    </span>
                                @endif
                            </div>
                            <form action="{{ route('webhooks.test', $webhook->id) }}" method="POST" class="inline">
                                @csrf
                                <button 
                                    type="submit"
                                    class="bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white px-6 py-2.5 rounded-xl font-semibold transition-all shadow-md hover:shadow-lg flex items-center"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Testar Agora
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Info Sidebar -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
        <!-- Events Info -->
        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 border-2 border-indigo-200 rounded-2xl p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <span class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </span>
                Eventos Disponíveis
            </h3>
            <div class="space-y-3">
                <div class="flex items-center p-3 bg-white rounded-xl border border-blue-200">
                    <span class="w-3 h-3 bg-blue-500 rounded-full mr-3"></span>
                    <code class="text-sm font-mono text-blue-700 font-semibold">transaction.created</code>
                </div>
                <div class="flex items-center p-3 bg-white rounded-xl border border-emerald-200">
                    <span class="w-3 h-3 bg-emerald-500 rounded-full mr-3"></span>
                    <code class="text-sm font-mono text-emerald-700 font-semibold">transaction.paid</code>
                </div>
                <div class="flex items-center p-3 bg-white rounded-xl border border-rose-200">
                    <span class="w-3 h-3 bg-rose-500 rounded-full mr-3"></span>
                    <code class="text-sm font-mono text-rose-700 font-semibold">transaction.failed</code>
                </div>
                <div class="flex items-center p-3 bg-white rounded-xl border border-amber-200">
                    <span class="w-3 h-3 bg-amber-500 rounded-full mr-3"></span>
                    <code class="text-sm font-mono text-amber-700 font-semibold">transaction.expired</code>
                </div>
                <div class="flex items-center p-3 bg-white rounded-xl border border-purple-200">
                    <span class="w-3 h-3 bg-purple-500 rounded-full mr-3"></span>
                    <code class="text-sm font-mono text-purple-700 font-semibold">transaction.refunded</code>
                </div>
            </div>
        </div>

        <!-- Payload Example -->
        <div class="bg-gradient-to-br from-slate-50 to-gray-100 border-2 border-slate-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <span class="w-10 h-10 bg-slate-600 rounded-xl flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </span>
                    Payload Exemplo
                </h3>
                <button 
                    onclick="copyToClipboard(document.getElementById('payloadExample').textContent, 'Payload copiado!')"
                    class="bg-slate-600 hover:bg-slate-700 text-white p-2 rounded-lg transition-colors"
                    title="Copiar payload"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </button>
            </div>
            <pre id="payloadExample" class="text-xs text-gray-700 overflow-x-auto bg-white p-4 rounded-xl border-2 border-slate-300 font-mono">{
  "event": "transaction.paid",
  "timestamp": "2025-06-25T15:30:45Z",
  "data": {
    "transaction_id": "PXB_ABC123",
    "amount": 100.00,
    "status": "paid",
    "customer": {
      "name": "João Silva",
      "email": "joao@exemplo.com"
    }
  }
}</pre>
        </div>
    </div>
</div>

<!-- Toast de notificação (copiar) -->
<div id="copyToast" class="fixed bottom-4 right-4 bg-gradient-to-r from-emerald-500 to-teal-500 text-white px-6 py-3 rounded-xl shadow-2xl transform translate-y-20 opacity-0 transition-all duration-300 z-50">
    <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span id="copyToastMessage" class="font-semibold">Copiado!</span>
    </div>
</div>

<!-- Add Webhook Modal -->
<div id="addWebhookModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl">
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-6 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white">Novo Webhook</h3>
                    <button onclick="closeAddWebhookModal()" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <form action="{{ route('webhooks.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                
                <div>
                    <label for="url" class="block text-sm font-semibold text-gray-700 mb-2">
                        URL do Webhook *
                    </label>
                    <input 
                        id="url" 
                        name="url" 
                        type="url" 
                        required 
                        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                        placeholder="https://seu-site.com/webhook"
                    >
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                        Descrição
                    </label>
                    <input 
                        id="description" 
                        name="description" 
                        type="text" 
                        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                        placeholder="Descrição opcional"
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        Eventos *
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center p-3 bg-gray-50 rounded-xl border-2 border-gray-200 hover:border-green-300 cursor-pointer transition-all">
                            <input type="checkbox" id="event_created" name="events[]" value="transaction.created" class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <span class="ml-3 text-sm font-medium text-gray-700">Transação Criada</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-xl border-2 border-gray-200 hover:border-green-300 cursor-pointer transition-all">
                            <input type="checkbox" id="event_paid" name="events[]" value="transaction.paid" class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded" checked>
                            <span class="ml-3 text-sm font-medium text-gray-700">Transação Paga</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-xl border-2 border-gray-200 hover:border-green-300 cursor-pointer transition-all">
                            <input type="checkbox" id="event_failed" name="events[]" value="transaction.failed" class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <span class="ml-3 text-sm font-medium text-gray-700">Transação Falhou</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-xl border-2 border-gray-200 hover:border-green-300 cursor-pointer transition-all">
                            <input type="checkbox" id="event_expired" name="events[]" value="transaction.expired" class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <span class="ml-3 text-sm font-medium text-gray-700">Transação Expirada</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-xl border-2 border-gray-200 hover:border-green-300 cursor-pointer transition-all">
                            <input type="checkbox" id="event_refunded" name="events[]" value="transaction.refunded" class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <span class="ml-3 text-sm font-medium text-gray-700">Transação Estornada</span>
                        </label>
                    </div>
                </div>
                
                <label class="flex items-center p-3 bg-emerald-50 rounded-xl border-2 border-emerald-200 cursor-pointer">
                    <input type="checkbox" id="is_active" name="is_active" value="1" class="h-5 w-5 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded" checked>
                    <span class="ml-3 text-sm font-semibold text-emerald-700">Webhook Ativo</span>
                </label>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button 
                        type="button" 
                        onclick="closeAddWebhookModal()"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold transition-all"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit" 
                        class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-8 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl"
                    >
                        Criar Webhook
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Webhook Modal -->
<div id="editWebhookModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl">
            <div class="bg-gradient-to-r from-blue-600 to-cyan-600 p-6 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white">Editar Webhook</h3>
                    <button onclick="closeEditWebhookModal()" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <form id="editWebhookForm" action="" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label for="edit_url" class="block text-sm font-semibold text-gray-700 mb-2">
                        URL do Webhook *
                    </label>
                    <input 
                        id="edit_url" 
                        name="url" 
                        type="url" 
                        required 
                        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="https://seu-site.com/webhook"
                    >
                </div>
                
                <div>
                    <label for="edit_description" class="block text-sm font-semibold text-gray-700 mb-2">
                        Descrição
                    </label>
                    <input 
                        id="edit_description" 
                        name="description" 
                        type="text" 
                        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="Descrição opcional"
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        Eventos *
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center p-3 bg-gray-50 rounded-xl border-2 border-gray-200 hover:border-blue-300 cursor-pointer transition-all">
                            <input type="checkbox" id="edit_event_created" name="events[]" value="transaction.created" class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-3 text-sm font-medium text-gray-700">Transação Criada</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-xl border-2 border-gray-200 hover:border-blue-300 cursor-pointer transition-all">
                            <input type="checkbox" id="edit_event_paid" name="events[]" value="transaction.paid" class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-3 text-sm font-medium text-gray-700">Transação Paga</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-xl border-2 border-gray-200 hover:border-blue-300 cursor-pointer transition-all">
                            <input type="checkbox" id="edit_event_failed" name="events[]" value="transaction.failed" class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-3 text-sm font-medium text-gray-700">Transação Falhou</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-xl border-2 border-gray-200 hover:border-blue-300 cursor-pointer transition-all">
                            <input type="checkbox" id="edit_event_expired" name="events[]" value="transaction.expired" class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-3 text-sm font-medium text-gray-700">Transação Expirada</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-xl border-2 border-gray-200 hover:border-blue-300 cursor-pointer transition-all">
                            <input type="checkbox" id="edit_event_refunded" name="events[]" value="transaction.refunded" class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-3 text-sm font-medium text-gray-700">Transação Estornada</span>
                        </label>
                    </div>
                </div>
                
                <label class="flex items-center p-3 bg-emerald-50 rounded-xl border-2 border-emerald-200 cursor-pointer">
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="h-5 w-5 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                    <span class="ml-3 text-sm font-semibold text-emerald-700">Webhook Ativo</span>
                </label>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button 
                        type="button" 
                        onclick="closeEditWebhookModal()"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold transition-all"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit" 
                        class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white px-8 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl"
                    >
                        Atualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Dispatch Webhook Modal -->
<div id="dispatchModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl">
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-6 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white">Disparar Teste</h3>
                    <button onclick="closeDispatchModal()" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <form action="{{ route('webhooks.dispatch') }}" method="POST" class="p-6 space-y-4">
                @csrf
                
                <div>
                    <label for="dispatch_transaction_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        ID da Transação *
                    </label>
                    <input 
                        id="dispatch_transaction_id" 
                        name="transaction_id" 
                        type="text" 
                        required 
                        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                        placeholder="PXB_ABC123"
                    >
                    <p class="text-xs text-gray-500 mt-2">Digite o ID da transação para disparar os webhooks</p>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button 
                        type="button" 
                        onclick="closeDispatchModal()"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold transition-all"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit" 
                        class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-8 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl"
                    >
                        Disparar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Função de copiar melhorada com toast
function copyToClipboard(text, message = 'Copiado!') {
    navigator.clipboard.writeText(text).then(function() {
        showToast(message);
    }).catch(function(err) {
        console.error('Erro ao copiar:', err);
        alert('Erro ao copiar para a área de transferência');
    });
}

function showToast(message) {
    const toast = document.getElementById('copyToast');
    const toastMessage = document.getElementById('copyToastMessage');
    
    toastMessage.textContent = message;
    toast.classList.remove('translate-y-20', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');
    
    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 3000);
}

// Modal functions
function openAddWebhookModal() {
    document.getElementById('addWebhookModal').classList.remove('hidden');
}

function closeAddWebhookModal() {
    document.getElementById('addWebhookModal').classList.add('hidden');
}

function openEditWebhookModal(id, url, description, events, isActive) {
    const modal = document.getElementById('editWebhookModal');
    const form = document.getElementById('editWebhookForm');
    
    form.action = `/settings/webhooks/${id}`;
    document.getElementById('edit_url').value = url;
    document.getElementById('edit_description').value = description || '';
    document.getElementById('edit_is_active').checked = isActive;
    
    // Reset all checkboxes
    document.querySelectorAll('#editWebhookModal input[name="events[]"]').forEach(checkbox => {
        checkbox.checked = events.includes(checkbox.value);
    });
    
    modal.classList.remove('hidden');
}

function closeEditWebhookModal() {
    document.getElementById('editWebhookModal').classList.add('hidden');
}

function openDispatchModal() {
    document.getElementById('dispatchModal').classList.remove('hidden');
}

function closeDispatchModal() {
    document.getElementById('dispatchModal').classList.add('hidden');
}

function confirmDeleteWebhook(id) {
    if (confirm('Tem certeza que deseja excluir este webhook?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/settings/webhooks/${id}`;
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        
        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        
        form.appendChild(csrf);
        form.appendChild(method);
        document.body.appendChild(form);
        form.submit();
    }
}

function confirmRegenerateSecret(id) {
    if (confirm('Tem certeza que deseja regenerar o secret? O secret atual será invalidado.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/settings/webhooks/${id}/regenerate-secret`;
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        
        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modals on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAddWebhookModal();
        closeEditWebhookModal();
        closeDispatchModal();
    }
});

// Close modals on outside click
document.querySelectorAll('[id$="Modal"]').forEach(modal => {
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.classList.add('hidden');
        }
    });
});
</script>
@endpush
@endsection
