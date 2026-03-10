@extends('layouts.dashboard')

@section('title', 'Comissões e Referidos')

@section('content')
<div class="flex-1 overflow-y-auto bg-[#000000] p-5">
    <div class="max-w-[1600px] mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-white mb-2" style="font-family: Manrope, sans-serif;">Comissões e Referidos</h1>
            <p class="text-sm text-[#AAAAAA]">Gerencie seus referidos e comissões</p>
        </div>

        <!-- Link de Referência -->
        <div class="bg-[#161616] rounded-2xl border border-[#1f1f1f] p-5 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-white mb-1">Seu Link de Referência</h2>
                    <p class="text-sm text-[#707070]">Compartilhe este link para ganhar comissões</p>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="flex-1 px-4 py-3 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg">
                    <input type="text" id="referralLink" value="{{ $user->referral_code ? url('/cadastro?ref=' . $user->referral_code) : 'Gerando código...' }}" readonly class="w-full bg-transparent text-white text-sm focus:outline-none">
                </div>
                <button onclick="copyReferralLink()" class="px-6 py-3 bg-[#D4AF37] hover:bg-[#b8010a] text-white rounded-lg text-sm font-semibold transition-colors" {{ empty($user->referral_code) ? 'disabled' : '' }}>
                    Copiar Link
                </button>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-[#707070]">Código:</span>
                <span class="text-xs font-semibold text-white">{{ $user->referral_code ?: 'Não disponível' }}</span>
                @if($user->referral_code)
                <button onclick="copyReferralCode()" class="ml-2 text-xs text-[#D4AF37] hover:text-[#b8010a] transition-colors">
                    Copiar Código
                </button>
                @endif
            </div>
        </div>

        <!-- Cards de Resumo -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Total em Comissões -->
            <div class="bg-[#161616] rounded-2xl border border-[#1f1f1f] p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-[#707070]">Total em Comissões</span>
                </div>
                <div class="text-2xl font-semibold text-white mb-4">R$ {{ number_format($totalCommissions, 2, ',', '.') }}</div>
                <form action="{{ route('referrals.request-withdrawal') }}" method="POST" onsubmit="return confirm('Deseja realmente solicitar o saque de todas as comissões pendentes?');">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-[#D4AF37] hover:bg-[#b8010a] text-white rounded-lg text-sm font-semibold transition-colors">
                        Solicitar Saque
                    </button>
                </form>
            </div>

            <!-- Taxa de Comissão -->
            <div class="bg-[#161616] rounded-2xl border border-[#1f1f1f] p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-[#707070]">Taxa de Comissão</span>
                </div>
                <div class="text-2xl font-semibold text-white mb-2">
                    {{ number_format($user->commission_percentage ?? 1.00, 2, ',', '.') }}%
                    @if($user->commission_fixed > 0)
                        + R$ {{ number_format($user->commission_fixed, 2, ',', '.') }}
                    @endif
                </div>
                <div class="text-xs text-[#707070]">Configuração padrão</div>
            </div>

            <!-- Qtde. de Referidos -->
            <div class="bg-[#161616] rounded-2xl border border-[#1f1f1f] p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-[#707070]">Qtde. de Referidos</span>
                </div>
                <div class="text-2xl font-semibold text-white mb-2">{{ $referredUsers->count() }}</div>
                <div class="text-xs text-[#707070]">Qtde. de Transações: {{ $referredUsers->sum('transactions_count') }}</div>
            </div>
        </div>

        <!-- Lista de Referidos -->
        <div class="bg-[#161616] rounded-2xl border border-[#1f1f1f] overflow-hidden">
            <div class="p-5 border-b border-[#1f1f1f]">
                <h2 class="text-lg font-semibold text-white">Referidos</h2>
            </div>
            
            @if($referredUsers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-[#1f1f1f] border-b border-[#2d2d2d]">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">Referido</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">Data de Adesão</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">Taxa de Comissão</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">Total de Comissão</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">Transações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[#161616] divide-y divide-[#1f1f1f]">
                            @foreach($referredUsers as $referred)
                                @php
                                    $totalCommission = \DB::table('referral_commissions')
                                        ->where('referrer_id', $user->id)
                                        ->where('referred_id', $referred->id)
                                        ->where('status', 'pending')
                                        ->sum('commission_amount');
                                @endphp
                                <tr class="hover:bg-[#1f1f1f] transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-white">{{ $referred->name }}</span>
                                            <span class="text-xs text-[#707070]">{{ $referred->email }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-white">{{ $referred->created_at->format('d M Y') }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-white">
                                            {{ number_format($referred->commission_percentage ?? 1.00, 2, ',', '.') }}%
                                            @if($referred->commission_fixed > 0)
                                                + R$ {{ number_format($referred->commission_fixed, 2, ',', '.') }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-semibold text-white">R$ {{ number_format($totalCommission, 2, ',', '.') }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-[#707070]">{{ $referred->transactions_count ?? 0 }} transações</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-10 text-center">
                    <p class="text-sm text-[#707070]">Nenhum referido encontrado.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function copyReferralLink() {
    const linkInput = document.getElementById('referralLink');
    linkInput.select();
    linkInput.setSelectionRange(0, 99999); // Para mobile
    
    navigator.clipboard.writeText(linkInput.value).then(function() {
        alert('Link copiado para a área de transferência!');
    }, function() {
        // Fallback para navegadores antigos
        document.execCommand('copy');
        alert('Link copiado para a área de transferência!');
    });
}

function copyReferralCode() {
    const code = '{{ $user->referral_code }}';
    navigator.clipboard.writeText(code).then(function() {
        alert('Código copiado para a área de transferência!');
    }, function() {
        // Fallback
        const textarea = document.createElement('textarea');
        textarea.value = code;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Código copiado para a área de transferência!');
    });
}

</script>
@endsection

