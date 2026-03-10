@extends('layouts.dashboard')

@section('title', 'Links de Pagamento')

@section('content')
<div class="flex-1 overflow-y-auto bg-[#000000] p-5">
    <div class="max-w-[1600px] mx-auto">
        <div class="bg-[#000000] rounded-2xl space-y-6">
            <!-- Header -->
            <div class="content-stretch flex flex-col md:flex-row items-start md:items-center justify-start md:justify-between relative size-full p-5 gap-6 md:gap-0">
                <div class="content-stretch flex flex-col gap-2.5 items-start justify-start leading-[0] relative shrink-0 text-nowrap">
                    <div class="font-['Manrope:Regular',_sans-serif] font-normal relative shrink-0 text-[28px] tracking-[-0.56px]">
                        <h1 class="leading-[1.2] text-nowrap whitespace-pre text-white">Links de Pagamento</h1>
                    </div>
                    <div class="font-['Manrope:SemiBold',_sans-serif] font-regular relative shrink-0 text-[12px] tracking-[-0.24px]">
                        <p class="leading-[1.3] text-nowrap whitespace-pre text-[#AAAAAA]">Crie e gerencie links de pagamento para seus clientes</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button onclick="openCreateModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#D4AF37] hover:bg-[#D4AF37] text-white rounded-lg text-sm font-semibold transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"></path>
                            <path d="M12 5v14"></path>
                        </svg>
                        Novo Link
                    </button>
                </div>
            </div>

            <!-- Lista de Links -->
            <div class="bg-[#161616] rounded-2xl border border-[#1f1f1f] overflow-hidden mx-5 mb-5">
                @if($paymentLinks->count() > 0)
                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-[#1f1f1f] border-b border-[#2d2d2d]">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">Nome</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">Valor</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">Link</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">Uso</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-[#707070] uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-[#161616] divide-y divide-[#1f1f1f]">
                                @foreach($paymentLinks as $link)
                                    <tr class="hover:bg-[#1f1f1f] transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-white text-sm">{{ $link->title }}</span>
                                                @if($link->description)
                                                    <span class="text-xs text-[#AAAAAA] mt-1">{{ strlen($link->description) > 50 ? substr($link->description, 0, 50) . '...' : $link->description }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($link->allow_custom_amount)
                                                <span class="text-sm text-[#AAAAAA]">Personalizado</span>
                                                @if($link->min_amount || $link->max_amount)
                                                    <div class="text-xs text-[#707070] mt-1">
                                                        @if($link->min_amount) Min: R$ {{ number_format($link->min_amount, 2, ',', '.') }} @endif
                                                        @if($link->max_amount) Max: R$ {{ number_format($link->max_amount, 2, ',', '.') }} @endif
                                                    </div>
                                                @endif
                                            @else
                                                <span class="font-bold text-white">R$ {{ number_format($link->amount, 2, ',', '.') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <input type="text" value="{{ $link->checkout_url }}" readonly onclick="this.select()" class="px-3 py-1.5 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white text-xs font-mono w-[300px] focus:outline-none focus:ring-2 focus:ring-[#D4AF37]">
                                                <button onclick="copyToClipboard('{{ $link->checkout_url }}')" class="px-3 py-1.5 bg-[#1f1f1f] hover:bg-[#2d2d2d] border border-[#2d2d2d] rounded-lg text-[#D4AF37] text-xs font-semibold transition-colors">
                                                    Copiar
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($link->is_active && $link->canBeUsed())
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-[#1f1f1f] text-[#22C672] border border-[#2d2d2d]">Ativo</span>
                                            @else
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-[#1f1f1f] text-[#707070] border border-[#2d2d2d]">Inativo</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-white">{{ $link->current_uses }}</span>
                                            @if($link->max_uses)
                                                <span class="text-xs text-[#707070]">/ {{ $link->max_uses }}</span>
                                            @else
                                                <span class="text-xs text-[#707070]">/ ∞</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <button onclick="getIntegrationCode('{{ $link->slug }}')" class="px-3 py-1.5 bg-[#1f1f1f] hover:bg-[#2d2d2d] border border-[#2d2d2d] rounded-lg text-[#D4AF37] text-xs font-semibold transition-colors">
                                                    Código
                                                </button>
                                                <button onclick="editLink({{ $link->id }})" class="px-3 py-1.5 bg-[#1f1f1f] hover:bg-[#2d2d2d] border border-[#2d2d2d] rounded-lg text-[#AAAAAA] text-xs font-semibold transition-colors">
                                                    Editar
                                                </button>
                                                <button onclick="deleteLink({{ $link->id }})" class="px-3 py-1.5 bg-[#1f1f1f] hover:bg-[#ff6b6b] border border-[#2d2d2d] rounded-lg text-[#ff6b6b] text-xs font-semibold transition-colors">
                                                    Excluir
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden divide-y divide-[#1f1f1f]">
                        @foreach($paymentLinks as $link)
                            <div class="p-4">
                                <div class="flex flex-col gap-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="font-semibold text-white text-sm">{{ $link->title }}</span>
                                            @if($link->is_active && $link->canBeUsed())
                                                <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-[#1f1f1f] text-[#22C672] border border-[#2d2d2d]">Ativo</span>
                                            @else
                                                <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-[#1f1f1f] text-[#707070] border border-[#2d2d2d]">Inativo</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-sm text-[#AAAAAA]">
                                        @if($link->allow_custom_amount)
                                            <p>Valor: <span class="text-white">Personalizado</span></p>
                                        @else
                                            <p>Valor: <span class="text-white font-bold">R$ {{ number_format($link->amount, 2, ',', '.') }}</span></p>
                                        @endif
                                        <p>Uso: <span class="text-white">{{ $link->current_uses }}</span> @if($link->max_uses)/ {{ $link->max_uses }}@else/ ∞@endif</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <button onclick="getIntegrationCode('{{ $link->slug }}')" class="px-3 py-1.5 bg-[#1f1f1f] hover:bg-[#2d2d2d] border border-[#2d2d2d] rounded-lg text-[#D4AF37] text-xs font-semibold transition-colors">
                                            Código
                                        </button>
                                        <button onclick="editLink({{ $link->id }})" class="px-3 py-1.5 bg-[#1f1f1f] hover:bg-[#2d2d2d] border border-[#2d2d2d] rounded-lg text-[#AAAAAA] text-xs font-semibold transition-colors">
                                            Editar
                                        </button>
                                        <button onclick="deleteLink({{ $link->id }})" class="px-3 py-1.5 bg-[#1f1f1f] hover:bg-[#ff6b6b] border border-[#2d2d2d] rounded-lg text-[#ff6b6b] text-xs font-semibold transition-colors">
                                            Excluir
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 bg-[#1f1f1f] border-t border-[#2d2d2d]">
                        {{ $paymentLinks->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-[#707070] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        <p class="text-xl font-semibold text-white mb-2">Nenhum link de pagamento encontrado</p>
                        <p class="text-[#AAAAAA] mb-4">Crie seu primeiro link de pagamento para começar a receber pagamentos.</p>
                        <button onclick="openCreateModal()" class="px-4 py-2 bg-[#D4AF37] hover:bg-[#D4AF37] text-white rounded-lg text-sm font-semibold transition-colors">
                            Criar Primeiro Link
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Criar/Editar Link -->
<div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-[#161616] rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto border border-[#1f1f1f]">
        <div class="bg-[#1f1f1f] p-6 rounded-t-2xl border-b border-[#2d2d2d]">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-medium text-white" id="modalTitle">Novo Link de Pagamento</h3>
                <button onclick="closeCreateModal()" class="text-[#AAAAAA] hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <form id="paymentLinkForm" class="p-6 space-y-6">
            @csrf
            <input type="hidden" id="linkId" name="id" value="">
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Coluna Esquerda - Formulário -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">Nome do Link (interno) *</label>
                        <input type="text" id="linkTitle" name="title" required class="w-full px-4 py-3 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37] placeholder-[#707070]" placeholder="Ex: Ebook de Receitas Fit">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">Descrição do Produto</label>
                        <textarea id="linkDescription" name="description" rows="3" class="w-full px-4 py-3 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37] placeholder-[#707070]" placeholder="Ex: Acesso ao Ebook Receitas Fit"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">Valor (R$)</label>
                            <input type="text" id="linkAmount" name="amount" inputmode="decimal" class="w-full px-4 py-3 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37] placeholder-[#707070]" placeholder="0,00" oninput="formatCurrency(this)">
                            <small class="text-xs text-[#707070] mt-1 block">Deixe vazio para permitir valor personalizado</small>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">Método de Pagamento *</label>
                            <select id="linkPaymentMethod" name="payment_method" required class="w-full px-4 py-3 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37]">
                                <option value="pix">PIX</option>
                                <option value="credit_card">Cartão de Crédito</option>
                                <option value="bank_slip">Boleto</option>
                                <option value="all">Todos</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="linkAllowCustom" name="allow_custom_amount" value="1" class="w-4 h-4 rounded border-[#2d2d2d] bg-[#1f1f1f] text-[#D4AF37] focus:ring-[#D4AF37]">
                            <span class="text-sm font-semibold text-[#AAAAAA]">Permitir que o cliente escolha o valor</span>
                        </label>
                    </div>

                    <div id="customAmountFields" class="hidden grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">Valor Mínimo (R$)</label>
                            <input type="text" id="linkMinAmount" name="min_amount" inputmode="decimal" class="w-full px-4 py-3 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37] placeholder-[#707070]" placeholder="0,00" oninput="formatCurrency(this)">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">Valor Máximo (R$)</label>
                            <input type="text" id="linkMaxAmount" name="max_amount" inputmode="decimal" class="w-full px-4 py-3 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37] placeholder-[#707070]" placeholder="0,00" oninput="formatCurrency(this)">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">Máximo de Usos</label>
                            <input type="number" id="linkMaxUses" name="max_uses" min="1" class="w-full px-4 py-3 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37] placeholder-[#707070]" placeholder="Ilimitado">
                            <small class="text-xs text-[#707070] mt-1 block">Deixe vazio para ilimitado</small>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">Data de Expiração</label>
                            <input type="datetime-local" id="linkExpiresAt" name="expires_at" class="w-full px-4 py-3 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37]">
                        </div>
                    </div>
                </div>

                <!-- Coluna Direita - Preview -->
                <div>
                    <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">Preview do Link</label>
                    <div id="modalPreviewContainer" class="bg-[#1f1f1f] border border-[#2d2d2d] rounded-xl p-6">
                        <div class="text-center space-y-4">
                            <h3 id="previewTitle" class="text-lg font-semibold text-white">Nome do Link</h3>
                            <p id="previewDescription" class="text-sm text-[#AAAAAA]">Descrição do produto</p>
                            <div class="bg-[#161616] border border-[#2d2d2d] rounded-lg p-4">
                                <p class="text-sm text-[#707070] mb-2">Valor</p>
                                <p id="previewAmount" class="text-2xl font-bold text-white">R$ 0,00</p>
                            </div>
                            <div class="bg-[#161616] border border-[#2d2d2d] rounded-lg p-4">
                                <p class="text-xs text-[#707070] mb-1">Link de Checkout</p>
                                <p id="previewLink" class="text-xs font-mono text-[#D4AF37] break-all">{{ url('/checkout') }}/...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-[#2d2d2d]">
                <button type="submit" class="flex-1 bg-[#D4AF37] hover:bg-[#D4AF37] text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                    Salvar Link
                </button>
                <button type="button" onclick="closeCreateModal()" class="px-6 py-3 bg-[#1f1f1f] hover:bg-[#2d2d2d] text-white rounded-lg font-semibold transition-colors border border-[#2d2d2d]">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Código de Integração -->
<div id="integrationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-[#161616] rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto border border-[#1f1f1f]">
        <div class="bg-[#1f1f1f] p-6 rounded-t-2xl border-b border-[#2d2d2d]">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-medium text-white">Código de Integração</h3>
                <button onclick="closeIntegrationModal()" class="text-[#AAAAAA] hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <div class="bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg p-4">
                <p class="text-sm text-[#AAAAAA] mb-2">Copie e cole este código no seu site:</p>
                <textarea id="integrationCode" readonly class="w-full h-48 px-4 py-3 bg-[#161616] border border-[#2d2d2d] rounded-lg text-white font-mono text-xs focus:outline-none resize-none"></textarea>
                <button onclick="copyIntegrationCode()" class="mt-3 px-4 py-2 bg-[#D4AF37] hover:bg-[#D4AF37] text-white rounded-lg text-sm font-semibold transition-colors">
                    Copiar Código
                </button>
            </div>
            <div class="bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg p-4">
                <p class="text-sm font-semibold text-[#AAAAAA] mb-2">Link Direto:</p>
                <div class="flex items-center gap-2">
                    <input type="text" id="directLink" readonly class="flex-1 px-4 py-2 bg-[#161616] border border-[#2d2d2d] rounded-lg text-white text-sm font-mono focus:outline-none" onclick="this.select()">
                    <button onclick="copyDirectLink()" class="px-4 py-2 bg-[#1f1f1f] hover:bg-[#2d2d2d] border border-[#2d2d2d] rounded-lg text-[#D4AF37] text-sm font-semibold transition-colors">
                        Copiar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentLinkSlug = '';

function formatCurrency(input) {
    let value = input.value.replace(/\D/g, '');
    value = (value / 100).toFixed(2) + '';
    value = value.replace(".", ",");
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    input.value = value;
}

function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Novo Link de Pagamento';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('paymentLinkForm').action = '{{ route("payment-links.store") }}';
    document.getElementById('paymentLinkForm').reset();
    document.getElementById('linkId').value = '';
    updatePreview();
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
}

function editLink(id) {
    fetch(`/payment-links/${id}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const link = data.payment_link;
            document.getElementById('linkId').value = link.id;
            document.getElementById('linkTitle').value = link.title || '';
            document.getElementById('linkDescription').value = link.description || '';
            document.getElementById('linkAmount').value = link.amount ? parseFloat(link.amount).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '';
            document.getElementById('linkPaymentMethod').value = link.payment_method || 'pix';
            document.getElementById('linkAllowCustom').checked = link.allow_custom_amount || false;
            document.getElementById('linkMinAmount').value = link.min_amount ? parseFloat(link.min_amount).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '';
            document.getElementById('linkMaxAmount').value = link.max_amount ? parseFloat(link.max_amount).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '';
            document.getElementById('linkMaxUses').value = link.max_uses || '';
            document.getElementById('linkExpiresAt').value = link.expires_at ? new Date(link.expires_at).toISOString().slice(0, 16) : '';
            
            document.getElementById('modalTitle').textContent = 'Editar Link de Pagamento';
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('paymentLinkForm').action = `/payment-links/${link.id}`;
            
            toggleCustomAmountFields();
            updatePreview();
            document.getElementById('createModal').classList.remove('hidden');
        }
    });
}

function deleteLink(id) {
    if (!confirm('Tem certeza que deseja excluir este link de pagamento?')) return;
    
    fetch(`/payment-links/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro ao excluir link: ' + (data.message || 'Erro desconhecido'));
        }
    });
}

function getIntegrationCode(slug) {
    currentLinkSlug = slug;
    const checkoutUrl = `{{ url('/checkout') }}/${slug}`;
    const integrationCode = `<!-- Link de Pagamento - Integração -->
<div id="payment-link-widget-${slug}"></div>
<script>
(function() {
    const widgetId = 'payment-link-widget-${slug}';
    const widget = document.getElementById(widgetId);
    if (!widget) return;
    
    const iframe = document.createElement('iframe');
    iframe.src = '${checkoutUrl}';
    iframe.style.width = '100%';
    iframe.style.border = 'none';
    iframe.style.minHeight = '600px';
    iframe.style.borderRadius = '8px';
    iframe.setAttribute('allowtransparency', 'true');
    iframe.setAttribute('scrolling', 'no');
    
    // Ajustar altura automaticamente
    window.addEventListener('message', function(event) {
        if (event.data && event.data.type === 'resize' && event.data.height) {
            iframe.style.height = event.data.height + 'px';
        }
    });
    
    widget.appendChild(iframe);
})();
<\/script>`;
    
    document.getElementById('integrationCode').value = integrationCode;
    document.getElementById('directLink').value = checkoutUrl;
    document.getElementById('integrationModal').classList.remove('hidden');
}

function closeIntegrationModal() {
    document.getElementById('integrationModal').classList.add('hidden');
}

function copyIntegrationCode() {
    document.getElementById('integrationCode').select();
    document.execCommand('copy');
    alert('Código copiado para a área de transferência!');
}

function copyDirectLink() {
    document.getElementById('directLink').select();
    document.execCommand('copy');
    alert('Link copiado para a área de transferência!');
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Link copiado para a área de transferência!');
    });
}

function toggleCustomAmountFields() {
    const allowCustom = document.getElementById('linkAllowCustom').checked;
    const customFields = document.getElementById('customAmountFields');
    const amountField = document.getElementById('linkAmount');
    
    if (allowCustom) {
        customFields.classList.remove('hidden');
        amountField.disabled = true;
        amountField.value = '';
    } else {
        customFields.classList.add('hidden');
        amountField.disabled = false;
    }
    updatePreview();
}

function updatePreview() {
    const title = document.getElementById('linkTitle').value || 'Nome do Link';
    const description = document.getElementById('linkDescription').value || 'Descrição do produto';
    const amount = document.getElementById('linkAmount').value || '0,00';
    const allowCustom = document.getElementById('linkAllowCustom').checked;
    
    document.getElementById('previewTitle').textContent = title;
    document.getElementById('previewDescription').textContent = description;
    
    if (allowCustom) {
        const min = document.getElementById('linkMinAmount').value || '0,00';
        const max = document.getElementById('linkMaxAmount').value || '∞';
        document.getElementById('previewAmount').textContent = `R$ ${min} - R$ ${max}`;
    } else {
        document.getElementById('previewAmount').textContent = `R$ ${amount}`;
    }
}

// Event listeners
document.getElementById('linkAllowCustom').addEventListener('change', toggleCustomAmountFields);
document.getElementById('linkTitle').addEventListener('input', updatePreview);
document.getElementById('linkDescription').addEventListener('input', updatePreview);
document.getElementById('linkAmount').addEventListener('input', updatePreview);
document.getElementById('linkMinAmount').addEventListener('input', updatePreview);
document.getElementById('linkMaxAmount').addEventListener('input', updatePreview);

// Form submission
document.getElementById('paymentLinkForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const method = formData.get('_method') || 'POST';
    const url = this.action;
    
    // Convert currency values
    ['amount', 'min_amount', 'max_amount'].forEach(field => {
        if (formData.get(field)) {
            formData.set(field, formData.get(field).replace(/\./g, '').replace(',', '.'));
        }
    });
    
    // Laravel uses _method for method spoofing
    if (method === 'PUT') {
        formData.append('_method', 'PUT');
    }
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro ao salvar: ' + (data.message || JSON.stringify(data.errors || 'Erro desconhecido')));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao salvar link. Tente novamente.');
    });
});
</script>
@endpush
@endsection

