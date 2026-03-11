<!-- Modals: PIX Key, PIX Withdrawal, Crypto Withdrawal -->

<!-- Modal: Nova/Editar Chave PIX -->
<div id="pixKeyModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 hidden backdrop-blur-sm bg-black/60">
    <div class="relative w-full max-w-md rounded-2xl p-[30px] bg-[#161616] border border-white/10 shadow-[0_0_50px_rgba(0,0,0,0.5)]">
        <div class="flex flex-col gap-4">
            <div class="flex justify-between items-start">
                <div class="flex flex-col gap-[7px]">
                    <span class="text-[20px] font-black font-['Poppins'] tracking-tight text-white" id="pixKeyModalTitle">NOVA CHAVE PIX</span>
                    <span class="text-[12px] font-bold font-['Poppins'] tracking-tight text-[#707070] uppercase">Configuração de recebimento</span>
                </div>
                <button onclick="closePixKeyModal()" class="text-[#707070] hover:text-[#D4AF37] transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x w-5 h-5">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="pixKeyForm" onsubmit="savePixKey(event)" class="flex flex-col gap-4 mt-2">
                <input type="hidden" id="pixKeyId" name="id">
                <div class="flex flex-col gap-2">
                    <span class="text-[10px] font-black text-[#707070] uppercase tracking-widest">Tipo de Chave</span>
                    <select id="pixKeyType" name="type" required class="w-full rounded-xl px-4 py-3 bg-[#1F1F1F] text-white border border-white/5 focus:border-[#D4AF37]/50 focus:ring-0 text-xs font-bold transition-all">
                        <option value="EMAIL">E-mail</option>
                        <option value="CPF">CPF</option>
                        <option value="CNPJ">CNPJ</option>
                        <option value="PHONE">Telefone</option>
                        <option value="EVP">Chave Aleatória</option>
                    </select>
                </div>
                <div class="flex flex-col gap-2">
                    <span class="text-[10px] font-black text-[#707070] uppercase tracking-widest">Valor da Chave</span>
                    <input id="pixKeyValue" name="key" type="text" required placeholder="Digite sua chave aqui" class="w-full rounded-xl px-4 py-3 bg-[#1F1F1F] text-white border border-white/5 focus:border-[#D4AF37]/50 focus:ring-0 text-xs font-bold transition-all placeholder:text-gray-700">
                </div>
                <div class="flex flex-col gap-2">
                    <span class="text-[10px] font-black text-[#707070] uppercase tracking-widest">Descrição (Opcional)</span>
                    <input id="pixKeyDescription" name="description" type="text" placeholder="Ex: Minha conta principal" class="w-full rounded-xl px-4 py-3 bg-[#1F1F1F] text-white border border-white/5 focus:border-[#D4AF37]/50 focus:ring-0 text-xs font-bold transition-all placeholder:text-gray-700">
                </div>
                <div class="flex flex-col gap-2.5 mt-6">
                    <button type="submit" class="w-full bg-gradient-to-r from-[#8a6d1d] to-[#D4AF37] text-white rounded-xl py-3 text-xs font-black uppercase tracking-widest hover:scale-[1.02] transition-transform">SALVAR CHAVE</button>
                    <button type="button" onclick="closePixKeyModal()" class="w-full bg-white/5 text-gray-500 rounded-xl py-3 text-xs font-black uppercase tracking-widest hover:bg-white/10 transition-colors">CANCELAR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Saque via PIX -->
<div id="pixWithdrawalModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 hidden backdrop-blur-sm bg-black/60">
    <div class="relative w-full max-w-md rounded-2xl p-[30px] bg-[#161616] border border-white/10 shadow-[0_0_50px_rgba(0,0,0,0.5)]">
        <div class="flex flex-col gap-4">
            <div class="flex justify-between items-start">
                <div class="flex flex-col gap-[7px]">
                    <span class="text-[20px] font-black font-['Poppins'] tracking-tight text-white uppercase">REALIZAR SAQUE PIX</span>
                    <span class="text-[10px] font-bold text-emerald-500 bg-emerald-500/10 px-2 py-1 rounded inline-block w-fit uppercase tracking-widest">Saldo: R$ <?php echo e(number_format($pixBalance ?? 0, 2, ',', '.')); ?></span>
                </div>
                <button onclick="closePixWithdrawalModal()" class="text-[#707070] hover:text-[#D4AF37] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form id="pixWithdrawalForm" onsubmit="submitPixWithdrawal(event)" class="flex flex-col gap-4 mt-2">
                <div class="flex flex-col gap-2">
                    <span class="text-[10px] font-black text-[#707070] uppercase tracking-widest">Valor do Saque</span>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#D4AF37] font-black text-xs">R$</span>
                        <input id="pixWithdrawalAmount" name="amount" type="number" step="0.01" min="10" required placeholder="0,00" class="w-full rounded-xl px-4 py-3 pl-10 bg-[#1F1F1F] text-white border border-white/5 focus:border-[#D4AF37]/50 focus:ring-0 font-black text-xl transition-all">
                    </div>
                    <span class="text-[10px] text-red-500/80 font-bold hidden" id="pixMinAmountWarning">O valor mínimo para saque é R$ 10,00</span>
                </div>
                <div class="flex flex-col gap-2">
                    <span class="text-[10px] font-black text-[#707070] uppercase tracking-widest">Tipo de Chave</span>
                    <select id="pixWithdrawalPixType" name="pix_type" required class="w-full rounded-xl px-4 py-3 bg-[#1F1F1F] text-white border border-white/5 focus:border-[#D4AF37]/50 focus:ring-0 text-xs font-bold transition-all">
                        <option value="">Selecione o tipo</option>
                        <option value="cpf">CPF</option>
                        <option value="email">E-mail</option>
                        <option value="phone">Telefone</option>
                        <option value="random">Chave Aleatória</option>
                    </select>
                </div>
                <div class="flex flex-col gap-2">
                    <span class="text-[10px] font-black text-[#707070] uppercase tracking-widest">Digite a Chave PIX</span>
                    <input id="pixWithdrawalPixKey" name="pix_key" type="text" required placeholder="Sua chave aqui" class="w-full rounded-xl px-4 py-3 bg-[#1F1F1F] text-white border border-white/5 focus:border-[#D4AF37]/50 focus:ring-0 text-xs font-bold transition-all">
                </div>
                <div class="bg-yellow-500/5 border border-yellow-500/10 p-3 rounded-xl flex gap-3 items-center">
                    <svg class="w-5 h-5 text-yellow-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-[10px] text-yellow-500/80 font-bold leading-tight">Certifique-se de que a chave PIX está correta. Transferências incorretas não podem ser estornadas.</span>
                </div>
                <div class="flex flex-col gap-2.5 mt-4">
                    <button type="submit" class="w-full bg-gradient-to-r from-[#8a6d1d] to-[#D4AF37] text-white rounded-xl py-4 text-xs font-black uppercase tracking-widest hover:scale-[1.02] transition-all shadow-[0_10px_20px_rgba(212,175,55,0.2)]">CONFIRMAR SAQUE</button>
                    <button type="button" onclick="closePixWithdrawalModal()" class="w-full bg-white/5 text-gray-500 rounded-xl py-3 text-xs font-black uppercase tracking-widest hover:bg-white/10 transition-colors">CANCELAR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Saque via Crypto (USDT) -->
<div id="cryptoWithdrawalModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 hidden backdrop-blur-sm bg-black/60">
    <div class="relative w-full max-w-md rounded-2xl p-[30px] bg-[#161616] border border-white/10 shadow-[0_0_50px_rgba(0,0,0,0.5)]">
        <div class="flex flex-col gap-4">
            <div class="flex justify-between items-start">
                <div class="flex flex-col gap-[7px]">
                    <span class="text-[20px] font-black font-['Poppins'] tracking-tight text-white uppercase">SAQUE VIA USDT</span>
                    <span class="text-[10px] font-bold text-[#D4AF37] bg-[#D4AF37]/10 px-2 py-1 rounded inline-block w-fit uppercase tracking-widest">Rede: TRC20</span>
                </div>
                <button onclick="closeCryptoWithdrawalModal()" class="text-[#707070] hover:text-[#D4AF37] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form id="cryptoWithdrawalForm" onsubmit="submitCryptoWithdrawal(event)" class="flex flex-col gap-4 mt-2">
                <div class="flex flex-col gap-2">
                    <span class="text-[10px] font-black text-[#707070] uppercase tracking-widest">Valor em REAIS (BRL)</span>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#D4AF37] font-black text-xs">R$</span>
                        <input id="cryptoWithdrawalAmount" name="amount" type="number" step="0.01" min="5000" required placeholder="5.000,00" class="w-full rounded-xl px-4 py-3 pl-10 bg-[#1F1F1F] text-white border border-white/5 focus:border-[#D4AF37]/50 focus:ring-0 font-black text-xl transition-all">
                    </div>
                </div>
                <div class="bg-black/40 border border-white/5 p-4 rounded-xl space-y-3">
                    <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-widest">
                        <span class="text-gray-500">Cotação USDT</span>
                        <span class="text-white" id="usdtQuote">R$ 5.68</span>
                    </div>
                    <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-widest">
                        <span class="text-gray-500">Taxa de Rede</span>
                        <span class="text-red-500">5% + R$ 25,00</span>
                    </div>
                    <div class="h-px bg-white/5"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">VOCÊ RECEBE</span>
                        <span class="text-lg font-black text-[#D4AF37]" id="cryptoReceiveAmount">0.00 USDT</span>
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <span class="text-[10px] font-black text-[#707070] uppercase tracking-widest">Endereço da Carteira (TRC20)</span>
                    <input id="cryptoWithdrawalWallet" name="wallet_id" type="text" required placeholder="T..." class="w-full rounded-xl px-4 py-3 bg-[#1F1F1F] text-white border border-white/5 focus:border-[#D4AF37]/50 focus:ring-0 text-[10px] font-black tracking-widest transition-all">
                </div>
                <div class="flex flex-col gap-2.5 mt-4">
                    <button type="submit" id="cryptoWithdrawalSubmitBtn" class="w-full bg-gradient-to-r from-[#8a6d1d] to-[#D4AF37] text-white rounded-xl py-4 text-xs font-black uppercase tracking-widest hover:scale-[1.02] transition-all shadow-[0_10px_20px_rgba(212,175,55,0.2)]">SOLICITAR SAQUE USDT</button>
                    <button type="button" onclick="closeCryptoWithdrawalModal()" class="w-full bg-white/5 text-gray-500 rounded-xl py-3 text-xs font-black uppercase tracking-widest hover:bg-white/10 transition-colors">CANCELAR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Mantendo as mesmas funções JS para compatibilidade com o backend
function closePixKeyModal() { document.getElementById('pixKeyModal').classList.add('hidden'); }
function closePixWithdrawalModal() { document.getElementById('pixWithdrawalModal').classList.add('hidden'); }
function closeCryptoWithdrawalModal() { document.getElementById('cryptoWithdrawalModal').classList.add('hidden'); }

function openNewPixKeyModal() {
    document.getElementById('pixKeyModalTitle').textContent = 'NOVA CHAVE PIX';
    document.getElementById('pixKeyId').value = '';
    document.getElementById('pixKeyForm').reset();
    document.getElementById('pixKeyModal').classList.remove('hidden');
}

function openPixWithdrawalModal() {
    document.getElementById('pixWithdrawalModal').classList.remove('hidden');
}

function openCryptoWithdrawalModal() {
    document.getElementById('cryptoWithdrawalModal').classList.remove('hidden');
    updateCryptoCalculations();
}

// Lógica de cálculo crypto ultra-rápida
const USDT_VAL = 5.68;
const MIN_BRL = 5000;

function updateCryptoCalculations() {
    const amountInput = document.getElementById('cryptoWithdrawalAmount');
    if (!amountInput) return;
    
    amountInput.addEventListener('input', function() {
        const brl = parseFloat(this.value) || 0;
        const fee = (brl * 0.05) + 25;
        const net = brl - fee;
        const usdt = net > 0 ? (net / USDT_VAL) : 0;
        
        document.getElementById('cryptoReceiveAmount').textContent = usdt.toFixed(2) + ' USDT';
    });
}

// Fechar modais no esc
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closePixKeyModal();
        closePixWithdrawalModal();
        closeCryptoWithdrawalModal();
    }
});
</script>
<?php /**PATH /home/painhodev/PlayPayments2.0/app/resources/views/wallet/modals.blade.php ENDPATH**/ ?>