<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Transação - playpayments Pay</title>
    <style>
        body { 
            margin: 0; 
            font-family: Arial, sans-serif; 
            background: #f5f6fa; 
            color: #333;
        }
        .navbar {
            background: #1e1f29;
            color: white;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div>
            <a href="{{ route('dashboard') }}">Home</a>
            <a href="{{ route('transactions.index') }}">Transações</a>
        </div>
        <div>
            <span>{{ auth()->user()->nome ?? auth()->user()->email }}</span>
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" style="background: none; border: none; color: white; cursor: pointer; margin-left: 15px;">Sair</button>
            </form>
        </div>
    </div>
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('dashboard') }}" style="color: #667eea; text-decoration: none;">← Voltar</a>
    </div>

    <h1 style="margin-bottom: 30px;">Detalhes da Transação #{{ $venda->id }}</h1>

    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;">
        <h2 style="margin-top: 0; color: #333;">Transação #{{ $venda->id }}</h2>
        
        <div style="margin-bottom: 20px;">
            @if($venda->status == 'pago')
                <span style="background: #28a745; color: white; padding: 8px 16px; border-radius: 4px; font-weight: bold;">✅ Pago</span>
            @elseif($venda->status == 'pendente')
                <span style="background: #ff9900; color: white; padding: 8px 16px; border-radius: 4px; font-weight: bold;">⏳ Pendente</span>
            @elseif($venda->status == 'falhou')
                <span style="background: #dc3545; color: white; padding: 8px 16px; border-radius: 4px; font-weight: bold;">❌ Falhou</span>
            @else
                <span style="background: #666; color: white; padding: 8px 16px; border-radius: 4px; font-weight: bold;">{{ ucfirst($venda->status) }}</span>
            @endif
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div>
                <strong style="color: #666; display: block; margin-bottom: 5px;">Valor bruto:</strong>
                <span style="font-size: 24px; font-weight: bold; color: #333;">R$ {{ number_format($venda->valor_bruto, 2, ',', '.') }}</span>
            </div>
            
            @if($venda->valor_liquido)
            <div>
                <strong style="color: #666; display: block; margin-bottom: 5px;">Valor líquido:</strong>
                <span style="font-size: 20px; font-weight: bold; color: #28a745;">R$ {{ number_format($venda->valor_liquido, 2, ',', '.') }}</span>
            </div>
            @endif
        </div>

        <div style="border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px;">
            <h3 style="margin-top: 0; margin-bottom: 15px;">Informações da Transação</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <strong style="color: #666; display: block; margin-bottom: 5px;">ID da Transação</strong>
                    <span>{{ $gatewayTransactionId ?? $venda->id }}</span>
                </div>
                <div>
                    <strong style="color: #666; display: block; margin-bottom: 5px;">Referência Externa</strong>
                    <span>{{ $externalRef }}</span>
                </div>
                @if($gatewayTransactionId)
                <div>
                    <strong style="color: #666; display: block; margin-bottom: 5px;">Gateway Transaction ID</strong>
                    <span>{{ $gatewayTransactionId }}</span>
                </div>
                @endif
                <div>
                    <strong style="color: #666; display: block; margin-bottom: 5px;">Criada em</strong>
                    <span>{{ \Carbon\Carbon::parse($venda->criado_em)->format('d/m/Y H:i') }}</span>
                </div>
                @if($venda->status == 'pago')
                <div>
                    <strong style="color: #666; display: block; margin-bottom: 5px;">Pago em</strong>
                    <span>{{ \Carbon\Carbon::parse($venda->criado_em)->format('d/m/Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- DADOS DO CLIENTE --}}
    @if($cliente)
    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;">
        <h2 style="margin-top: 0; color: #333;">Cliente</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div>
                <strong style="color: #666; display: block; margin-bottom: 5px;">Nome</strong>
                <span>{{ $cliente->nome }}</span>
            </div>
            <div>
                <strong style="color: #666; display: block; margin-bottom: 5px;">Email</strong>
                <span>{{ $cliente->email }}</span>
            </div>
            @if($cliente->telefone)
            <div>
                <strong style="color: #666; display: block; margin-bottom: 5px;">Telefone</strong>
                <span>{{ $cliente->telefone }}</span>
            </div>
            @endif
            @if($cliente->cpf)
            <div>
                <strong style="color: #666; display: block; margin-bottom: 5px;">CPF/CNPJ</strong>
                <span>{{ $cliente->cpf }}</span>
            </div>
            @endif
            @if($cliente->endereco)
            <div style="grid-column: 1 / -1;">
                <strong style="color: #666; display: block; margin-bottom: 5px;">Endereço</strong>
                <span>{{ $cliente->endereco }}</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- DADOS DA COMPANY (VENDEDOR) --}}
    @if($pj)
    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;">
        <h2 style="margin-top: 0; color: #333;">Vendedor (Company)</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div>
                <strong style="color: #666; display: block; margin-bottom: 5px;">Razão Social</strong>
                <span>{{ $pj->razao_social ?? 'Não informado' }}</span>
            </div>
            @if($pj->cnpj)
            <div>
                <strong style="color: #666; display: block; margin-bottom: 5px;">CNPJ</strong>
                <span>{{ $pj->cnpj }}</span>
            </div>
            @endif
            @if($pj->responsavel_telefone)
            <div>
                <strong style="color: #666; display: block; margin-bottom: 5px;">Telefone</strong>
                <span>{{ $pj->responsavel_telefone }}</span>
            </div>
            @endif
            @if($endereco)
            <div style="grid-column: 1 / -1;">
                <strong style="color: #666; display: block; margin-bottom: 5px;">Endereço</strong>
                <span>
                    {{ $endereco->rua ?? '' }}, {{ $endereco->numero ?? '' }} - {{ $endereco->bairro ?? '' }}, 
                    {{ $endereco->cidade ?? '' }}, {{ $endereco->estado ?? '' }} - {{ $endereco->cep ?? '' }}
                </span>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- PIX --}}
    @if($transacao && $transacao->chave_pix)
    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;">
        <h2 style="margin-top: 0; color: #333;">Meio de Pagamento</h2>
        <div style="margin-bottom: 20px;">
            <strong style="color: #666; display: block; margin-bottom: 5px;">Tipo</strong>
            <span style="font-size: 18px; font-weight: bold;">Pix</span>
        </div>
        
        @if($transacao->status == 'pendente')
        <div style="margin-bottom: 20px;">
            <strong style="color: #666; display: block; margin-bottom: 5px;">Vencimento</strong>
            <span>{{ \Carbon\Carbon::parse($transacao->created_at)->addHour()->format('d/m/Y H:i') }}</span>
        </div>
        @endif

        <div style="margin-bottom: 20px;">
            <strong style="color: #666; display: block; margin-bottom: 10px;">Copia e Cola</strong>
            <div style="display: flex; gap: 10px;">
                <input type="text" 
                       id="pixCode" 
                       value="{{ $transacao->chave_pix }}" 
                       readonly 
                       style="flex: 1; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; font-family: monospace; background: #f8f9fa;">
                <button onclick="copiarPix()" 
                        style="padding: 12px 20px; background: #667eea; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    📋 Copiar
                </button>
            </div>
        </div>

        @if($transacao->qr_code)
        <div style="margin-bottom: 20px;">
            <strong style="color: #666; display: block; margin-bottom: 10px;">QR Code</strong>
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <img src="data:image/png;base64,{{ $transacao->qr_code }}" 
                     alt="QR Code PIX" 
                     style="max-width: 300px; border: 3px solid #ddd; border-radius: 8px; padding: 10px; background: white;">
            </div>
            <button onclick="visualizarQRCode()" 
                    style="margin-top: 10px; padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer;">
                Visualizar QRCode
            </button>
        </div>
        @endif
    </div>
    @endif

    {{-- CARRINHO/ITENS --}}
    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="margin-top: 0; color: #333;">Carrinho</h2>
        <div style="border: 1px solid #eee; border-radius: 6px; padding: 15px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee;">
                <span>Pagamento via API</span>
                <span style="font-weight: bold;">R$ {{ number_format($venda->valor_bruto, 2, ',', '.') }} x1</span>
            </div>
        </div>
    </div>
</div>

<script>
    function copiarPix() {
        const pixCode = document.getElementById('pixCode');
        pixCode.select();
        pixCode.setSelectionRange(0, 99999);
        
        try {
            document.execCommand('copy');
            alert('✅ Código PIX copiado com sucesso!');
        } catch (err) {
            navigator.clipboard.writeText(pixCode.value).then(function() {
                alert('✅ Código PIX copiado com sucesso!');
            }, function(err) {
                alert('❌ Erro ao copiar. Tente selecionar e copiar manualmente.');
            });
        }
    }

    function visualizarQRCode() {
        // Abre o QR code em uma nova janela
        const qrCode = document.querySelector('img[alt="QR Code PIX"]');
        if (qrCode) {
            window.open(qrCode.src, '_blank');
        }
    }
</script>
</body>
</html>

