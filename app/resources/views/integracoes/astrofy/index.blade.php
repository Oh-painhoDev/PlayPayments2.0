@extends('layouts.dashboard')

@section('title', 'Astrofy - Integrações')

@section('content')
<section class="bg-view">
    <div class="flex-1 bg-[#000000] p-5 font-manrope">
        <div class="max-w-[1600px] mx-auto">
            <div class="bg-[#000000] rounded-2xl p-5 space-y-8">
                <div class="flex flex-col gap-6 mb-8">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('integracoes') }}">
                            <button class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:text-accent-foreground h-10 px-4 py-2 rounded-[8px] group gap-2 hover:bg-white/5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left transition-transform group-hover:-translate-x-1">
                                    <path d="m12 19-7-7 7-7"></path>
                                    <path d="M19 12H5"></path>
                                </svg>
                                <span class="text-white">Voltar</span>
                            </button>
                        </a>
                    </div>
                    <div class="flex items-start gap-6">
                        <div class="flex-shrink-0 w-16 h-16 rounded-xl p-3 bg-gradient-to-br from-gray-800/30 to-transparent">
                            <div style="color: #D4AF37; font-weight: bold; font-size: 20px; text-align: center; line-height: 48px;">Astrofy</div>
                        </div>
                        <div>
                            <h1 class="font-['Manrope'] font-medium text-[28px] tracking-[-0.56px] text-white">Astrofy</h1>
                            <p class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#AAAAAA]">Gateway Hub para integração de pagamentos</p>
                            <p class="font-['Manrope'] font-semibold text-[11px] tracking-[-0.22px] text-[#707070] mt-1">
                                ⚠️ Certifique-se de ter um gateway de pagamento configurado na sua conta. As credenciais do seu gateway serão usadas automaticamente.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="text-card-foreground shadow-sm overflow-hidden bg-[#161616] border-0 rounded-2xl">
                    <div class="p-6 bg-[#161616]">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <h3 class="font-['Manrope'] font-medium text-[16px] tracking-[-0.32px] text-white">Suas Integrações</h3>
                            <div class="flex-shrink-0">
                                @if($hasGateway)
                                    <button onclick="openModal()" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 text-white transition-all duration-200 h-10 px-4 py-2 rounded-[8px] gap-2 hover:bg-[#D4AF37]/90" style="background-color: #D4AF37; color: white;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus">
                                            <path d="M5 12h14"></path>
                                            <path d="M12 5v14"></path>
                                        </svg>
                                        Nova Integração
                                    </button>
                                @else
                                    <p class="text-yellow-400 text-sm">Configure um gateway primeiro</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="px-6 pb-6 bg-[#161616]">
                        <div class="space-y-4">
                            <div class="rounded-2xl overflow-hidden bg-[#161616]">
                                <div class="relative w-full overflow-auto">
                                    <table class="w-full caption-bottom text-sm border-separate border-spacing-y-2">
                                        <thead class="[&_tr]:border-b">
                                            <tr class="transition-colors data-[state=selected]:bg-muted bg-[#161616] hover:bg-[#161616] border-0">
                                                <th class="h-12 text-left align-middle [&:has([role=checkbox])]:pr-0 w-[200px] py-3 px-6 font-['Manrope'] font-medium text-[12px] tracking-[-0.24px] text-[#AAAAAA]">Nome</th>
                                                <th class="h-12 text-left align-middle [&:has([role=checkbox])]:pr-0 w-[150px] py-3 px-6 font-['Manrope'] font-medium text-[12px] tracking-[-0.24px] text-[#AAAAAA]">Gateway Key</th>
                                                <th class="h-12 text-left align-middle [&:has([role=checkbox])]:pr-0 w-[100px] py-3 px-6 font-['Manrope'] font-medium text-[12px] tracking-[-0.24px] text-[#AAAAAA]">Métodos</th>
                                                <th class="h-12 text-left align-middle [&:has([role=checkbox])]:pr-0 w-[120px] py-3 px-6 font-['Manrope'] font-medium text-[12px] tracking-[-0.24px] text-[#AAAAAA]">Status</th>
                                                <th class="h-12 text-left align-middle [&:has([role=checkbox])]:pr-0 w-[120px] py-3 px-6 font-['Manrope'] font-medium text-[12px] tracking-[-0.24px] text-[#AAAAAA]">Data de criação</th>
                                                <th class="h-12 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0 w-[50px] py-3 px-6"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="[&_tr:last-child]:border-0 pt-2" id="integrationsTableBody">
                                            @forelse($integrations as $integration)
                                                <tr class="bg-[#1F1F1F] hover:bg-[#252525] transition-colors rounded-lg" 
                                                    data-integration-id="{{ $integration->id }}"
                                                    data-integration-name="{{ $integration->name }}"
                                                    data-integration-gateway-key="{{ $integration->gateway_key ?? '' }}"
                                                    data-integration-base-url="{{ $integration->base_url }}">
                                                    <td class="py-3 px-6 font-['Manrope'] font-semibold text-[14px] tracking-[-0.28px] text-white">{{ $integration->name }}</td>
                                                    <td class="py-3 px-6 font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">
                                                        @if($integration->gateway_key)
                                                            <span class="text-green-400">✓ {{ substr($integration->gateway_key, 0, 20) }}...</span>
                                                        @else
                                                            <span class="text-[#707070]">Aguardando registro na Astrofy</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 px-6 font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">
                                                        PIX
                                                    </td>
                                                    <td class="py-3 px-6">
                                                        @if($integration->is_active)
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-400">Ativo</span>
                                                        @else
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-500/20 text-gray-400">Inativo</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 px-6 font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">
                                                        {{ $integration->created_at->format('d/m/Y') }}
                                                    </td>
                                                    <td class="py-3 px-6">
                                                        <div class="flex items-center gap-2">
                                                            @if(!$integration->gateway_key)
                                                                <button onclick="showAstrofyInfo({{ $integration->id }}, '{{ $integration->name }}', '{{ $integration->base_url }}')" class="text-[#707070] hover:text-blue-400 transition-colors" title="Ver informações para Astrofy">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                        <circle cx="12" cy="12" r="10"></circle>
                                                                        <path d="M12 16v-4"></path>
                                                                        <path d="M12 8h.01"></path>
                                                                    </svg>
                                                                </button>
                                                            @endif
                                                            <button onclick="editIntegration(this)" class="text-[#707070] hover:text-white transition-colors">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                                </svg>
                                                            </button>
                                                            <form action="{{ route('integracoes.astrofy.destroy', $integration->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover esta integração?');" class="inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="text-[#707070] hover:text-red-500 transition-colors">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                        <path d="M3 6h18"></path>
                                                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                                                    </svg>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="py-8 px-6 text-center">
                                                        <p class="font-['Manrope'] font-semibold text-[14px] tracking-[-0.28px] text-[#707070]">Nenhuma integração Astrofy encontrada</p>
                                                        @if($hasGateway)
                                                            <button onclick="openModal()" class="mt-4 inline-flex items-center justify-center gap-2 text-sm font-medium text-white transition-all duration-200 h-10 px-4 py-2 rounded-xl hover:bg-[#D4AF37]/90" style="background-color: #D4AF37;">
                                                                Criar primeira integração
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="astrofyModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50" onclick="closeModalOnBackdrop(event)">
    <div class="grid gap-4 sm:rounded-lg fixed left-[50%] top-[50%] z-50 w-[95%] max-w-[600px] -translate-x-1/2 -translate-y-1/2 rounded-2xl border p-6 shadow-lg duration-200 bg-[#161616] border-[#1f1f1f]" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b pb-4 border-[#1f1f1f]">
            <h2 id="modalTitle" class="font-['Manrope'] font-semibold text-[20px] tracking-[-0.4px] text-white">Nova Integração</h2>
            <button type="button" onclick="closeModal()" class="absolute right-4 top-4 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x h-4 w-4">
                    <path d="M18 6 6 18"></path>
                    <path d="m6 6 12 12"></path>
                </svg>
                <span class="sr-only">Close</span>
            </button>
        </div>
        @if(!$hasGateway)
            <div class="p-4 rounded-xl bg-yellow-500/20 border border-yellow-500/50">
                <p class="text-yellow-400 text-sm">
                    ⚠️ Você precisa configurar um gateway de pagamento primeiro antes de criar uma integração Astrofy.
                </p>
            </div>
        @else
            <form id="astrofyForm" method="POST" action="" class="mt-6 flex flex-col gap-6">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="integration_id" id="integrationId">
                
                <div class="space-y-2">
                    <label class="font-['Manrope'] font-semibold text-[14px] tracking-[-0.28px] text-white">Nome da Integração <span class="text-red-500">*</span></label>
                    <div class="flex flex-col gap-1.5">
                        <input name="name" id="integrationName" type="text" class="flex h-10 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 w-full rounded-xl border-2 bg-[#1f1f1f] border-[#2a2a2a] text-white placeholder:text-[#707070] focus:border-[#D4AF37]" placeholder="Ex: Meu Gateway Astrofy" value="" required>
                        <p class="text-xs text-[#707070]">
                            Escolha um nome para identificar esta integração.
                        </p>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-[#1f1f1f] border border-[#2a2a2a]">
                    <p class="text-white text-sm mb-3">
                        <strong>📋 Informações para adicionar na Astrofy:</strong>
                    </p>
                    <div class="space-y-2 text-xs text-[#AAAAAA]">
                        <p><strong class="text-white">Base URL:</strong> <code class="text-[#D4AF37]">{{ $baseUrl }}</code></p>
                        <p><strong class="text-white">Métodos de Pagamento:</strong> PIX</p>
                        <p class="mt-3 text-[#707070]">
                            Após criar a integração, você receberá um Gateway Key da Astrofy. Cole esse key no campo abaixo quando receber.
                        </p>
                    </div>
                </div>

                <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 text-white transition-all duration-200 h-10 px-4 py-2 mt-4 w-full rounded-xl font-['Manrope'] font-semibold text-[14px] tracking-[-0.28px] hover:bg-[#D4AF37]/90" style="background-color: #D4AF37; color: white;">
                    Criar Integração
                </button>
            </form>
        @endif
    </div>
</div>

@if(session('success'))
    <div id="successMessage" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        {{ session('success') }}
    </div>
    <script>
        setTimeout(() => {
            document.getElementById('successMessage')?.remove();
        }, 5000);
    </script>
@endif

@push('scripts')
<script>
    function openModal(integrationData = null) {
        const modal = document.getElementById('astrofyModal');
        const form = document.getElementById('astrofyForm');
        const modalTitle = document.getElementById('modalTitle');
        const formMethod = document.getElementById('formMethod');
        const integrationIdInput = document.getElementById('integrationId');
        
        if (integrationData) {
            modalTitle.textContent = 'Editar Integração';
            formMethod.value = 'PUT';
            integrationIdInput.value = integrationData.id;
            document.getElementById('integrationName').value = integrationData.name || '';
            
            // Adicionar campo para Gateway Key se não tiver
            let gatewayKeyField = document.getElementById('gatewayKey');
            if (!gatewayKeyField) {
                const nameField = document.getElementById('integrationName').closest('.space-y-2');
                const gatewayKeyDiv = document.createElement('div');
                gatewayKeyDiv.className = 'space-y-2';
                gatewayKeyDiv.innerHTML = `
                    <label class="font-['Manrope'] font-semibold text-[14px] tracking-[-0.28px] text-white">Gateway Key (X-Gateway-Key)</label>
                    <div class="flex flex-col gap-1.5">
                        <input name="gateway_key" id="gatewayKey" type="text" class="flex h-10 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 w-full rounded-xl border-2 bg-[#1f1f1f] border-[#2a2a2a] text-white placeholder:text-[#707070] focus:border-[#D4AF37]" placeholder="Cole o Gateway Key recebido da Astrofy" value="">
                        <p class="text-xs text-[#707070]">
                            Cole aqui o Gateway Key que você recebeu após registrar na Astrofy.
                        </p>
                    </div>
                `;
                nameField.after(gatewayKeyDiv);
            }
            gatewayKeyField = document.getElementById('gatewayKey');
            if (gatewayKeyField) {
                gatewayKeyField.value = integrationData.gatewayKey || '';
            }
            
            form.action = `{{ url('integracoes/astrofy') }}/${integrationData.id}`;
        } else {
            modalTitle.textContent = 'Nova Integração';
            formMethod.value = 'POST';
            integrationIdInput.value = '';
            form.reset();
            
            // Remover campo Gateway Key se existir
            const gatewayKeyField = document.getElementById('gatewayKey');
            if (gatewayKeyField) {
                gatewayKeyField.closest('.space-y-2').remove();
            }
            
            form.action = '{{ route("integracoes.astrofy.store") }}';
        }
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    function closeModal() {
        const modal = document.getElementById('astrofyModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    
    function closeModalOnBackdrop(event) {
        if (event.target === event.currentTarget) {
            closeModal();
        }
    }
    
    function editIntegration(button) {
        const row = button.closest('tr');
        const integrationData = {
            id: row.getAttribute('data-integration-id'),
            name: row.getAttribute('data-integration-name'),
            gatewayKey: row.getAttribute('data-integration-gateway-key') || ''
        };
        openModal(integrationData);
    }
    
    function showAstrofyInfo(integrationId, name, baseUrl) {
        const info = `📋 Informações para adicionar na Astrofy:

Nome: ${name}
Base URL: ${baseUrl}
Métodos de Pagamento: PIX

1. Acesse: https://gatewayhub.astrofy.site
2. Faça o registro do seu gateway usando:
   POST /v1/gateway
   
   Headers:
   X-Gateway-Key: (você receberá após o registro)
   
   Body:
   {
     "name": "${name}",
     "baseUrl": "${baseUrl}",
     "paymentTypes": ["PIX"]
   }

3. Após o registro, você receberá um Gateway Key
4. Volte aqui e edite a integração para adicionar o Gateway Key`;
        
        alert(info);
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>
@endpush
@endsection

