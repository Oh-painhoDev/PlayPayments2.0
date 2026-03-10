<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovante de Pagamento - {{ $transaction->transaction_id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            color: #000;
            background: #fff;
            padding: 40px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
        }
        
        .header {
            text-align: left;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #000;
        }
        
        .header .status {
            font-size: 14px;
            color: #000;
            margin-top: 5px;
        }
        
        .amount {
            text-align: left;
            font-size: 32px;
            font-weight: bold;
            margin: 20px 0 30px 0;
            color: #000;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
            color: #000;
        }
        
        .row {
            margin-bottom: 10px;
            padding: 5px 0;
        }
        
        .label {
            font-weight: bold;
            color: #000;
            display: inline-block;
            width: 150px;
        }
        
        .value {
            color: #000;
            display: inline-block;
        }
        
        .document-section {
            margin-top: 30px;
        }
        
        .document-row {
            margin-bottom: 8px;
            padding: 4px 0;
        }
        
        .document-label {
            font-weight: bold;
            color: #000;
            display: inline-block;
            width: 180px;
        }
        
        .document-value {
            color: #000;
            display: inline-block;
            word-break: break-all;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            text-align: left;
            font-size: 11px;
            color: #666;
        }
        
        @media print {
            body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Comprovante de Pagamento</h1>
            @if($transaction->paid_at)
            <div class="status">Pago em {{ $transaction->paid_at->format('d/m/Y H:i') }}</div>
            @elseif($transaction->status === 'pending')
            <div class="status">Pendente</div>
            @else
            <div class="status">{{ ucfirst($transaction->status) }}</div>
            @endif
        </div>
        
        <!-- Amount -->
        <div class="amount">
            R$ {{ number_format($transaction->amount, 2, ',', '.') }}
        </div>
        
        <div style="margin-bottom: 30px;"></div>
        
        <!-- PAGADOR -->
        <div class="section">
            <div class="section-title">PAGADOR</div>
            
            <div class="row">
                <div class="label">Nome</div>
                <div class="value">{{ $transaction->customer_data['name'] ?? 'N/A' }}</div>
            </div>
            
            <div class="row">
                <div class="label">CPF/CNPJ</div>
                <div class="value">{{ $customerDocument ?: 'N/A' }}</div>
            </div>
            
            <div class="row">
                <div class="label">Email</div>
                <div class="value">{{ $transaction->customer_data['email'] ?? 'N/A' }}</div>
            </div>
            
            @if(isset($transaction->customer_data['phone']) && $transaction->customer_data['phone'])
            <div class="row">
                <div class="label">Telefone</div>
                <div class="value">{{ $transaction->customer_data['phone'] }}</div>
            </div>
            @endif
            
            @if($customerAddress && !empty($customerAddress['street']))
            <div class="row">
                <div class="label">Endereço</div>
                <div class="value">{{ trim(($customerAddress['street'] ?? '') . ', ' . ($customerAddress['number'] ?? ''), ', ') }}</div>
            </div>
            
            <div class="row">
                <div class="label">Complemento</div>
                <div class="value">{{ !empty($customerAddress['complement']) ? $customerAddress['complement'] : 'Sem complemento' }}</div>
            </div>
            
            @if(!empty($customerAddress['neighborhood']))
            <div class="row">
                <div class="label">Bairro</div>
                <div class="value">{{ $customerAddress['neighborhood'] }}</div>
            </div>
            @endif
            
            @if(!empty($customerAddress['zipcode']))
            <div class="row">
                <div class="label">CEP</div>
                <div class="value">{{ $customerAddress['zipcode'] }}</div>
            </div>
            @endif
            
            @if(!empty($customerAddress['city']) || !empty($customerAddress['state']))
            <div class="row">
                <div class="label">Cidade/UF</div>
                <div class="value">{{ trim(($customerAddress['city'] ?? '') . '/' . ($customerAddress['state'] ?? ''), '/') }}</div>
            </div>
            @endif
            @elseif(isset($transaction->customer_data['address']) && is_string($transaction->customer_data['address']))
            <div class="row">
                <div class="label">Endereço</div>
                <div class="value">{{ $transaction->customer_data['address'] }}</div>
            </div>
            <div class="row">
                <div class="label">Complemento</div>
                <div class="value">Sem complemento</div>
            </div>
            @else
            <div class="row">
                <div class="label">Endereço</div>
                <div class="value">Não informado</div>
            </div>
            <div class="row">
                <div class="label">Complemento</div>
                <div class="value">Sem complemento</div>
            </div>
            @endif
        </div>
        
        <!-- FAVORECIDO -->
        <div class="section">
            <div class="section-title">FAVORECIDO</div>
            
            <div class="row">
                <div class="label">Nome</div>
                <div class="value">{{ $user->name }}</div>
            </div>
            
            @if($user->isPessoaJuridica())
            <div class="row">
                <div class="label">CNPJ</div>
                <div class="value">{{ $favoredDocument ?: 'N/A' }}</div>
            </div>
            @else
            <div class="row">
                <div class="label">CPF</div>
                <div class="value">{{ $favoredDocument ?: 'N/A' }}</div>
            </div>
            @endif
            
            @if($favoredPhone)
            <div class="row">
                <div class="label">Telefone</div>
                <div class="value">{{ $favoredPhone }}</div>
            </div>
            @endif
        </div>
        
        <!-- DOCUMENTO -->
        <div class="section document-section">
            <div class="section-title">DOCUMENTO</div>
            
            <div class="document-row">
                <div class="document-label">ID da Transação</div>
                <div class="document-value">{{ $transaction->transaction_id }}</div>
            </div>
            
            @if($transaction->payment_method === 'pix' && $pixPayload)
            <div class="document-row">
                <div class="document-label">Chave PIX</div>
                <div class="document-value">{{ $pixPayload }}</div>
            </div>
            @endif
            
            <div class="document-row">
                <div class="document-label">ID End to End</div>
                <div class="document-value">{{ $endToEndId ?: '-' }}</div>
            </div>
            
            <div class="document-row">
                <div class="document-label">Código de Autenticação</div>
                <div class="document-value">{{ $authCode ?: '-' }}</div>
            </div>
            
            @if($transaction->external_id)
            <div class="document-row">
                <div class="document-label">Referência Externa</div>
                <div class="document-value">{{ $transaction->external_id }}</div>
            </div>
            @endif
            
            <div class="document-row">
                <div class="document-label">Data de Criação</div>
                <div class="document-value">{{ $transaction->created_at->format('d/m/Y H:i') }}</div>
            </div>
            
            @if($transaction->paid_at)
            <div class="document-row">
                <div class="document-label">Data de Pagamento</div>
                <div class="document-value">{{ $transaction->paid_at->format('d/m/Y H:i') }}</div>
            </div>
            @endif
            
            @if($transaction->expires_at)
            <div class="document-row">
                <div class="document-label">Data de Expiração</div>
                <div class="document-value">{{ $transaction->expires_at->format('d/m/Y H:i') }}</div>
            </div>
            @endif
            
            <div class="document-row">
                <div class="document-label">Método de Pagamento</div>
                <div class="document-value">{{ strtoupper($transaction->payment_method === 'pix' ? 'PIX' : ($transaction->payment_method === 'credit_card' ? 'Cartão de Crédito' : 'Boleto')) }}</div>
            </div>
            
            <div class="document-row">
                <div class="document-label">Valor Bruto</div>
                <div class="document-value">R$ {{ number_format($transaction->amount, 2, ',', '.') }}</div>
            </div>
            
            <div class="document-row">
                <div class="document-label">Taxa</div>
                <div class="document-value">R$ {{ number_format($transaction->fee_amount, 2, ',', '.') }}</div>
            </div>
            
            <div class="document-row">
                <div class="document-label">Valor Líquido</div>
                <div class="document-value">R$ {{ number_format($transaction->net_amount, 2, ',', '.') }}</div>
            </div>
            
            <div class="document-row">
                <div class="document-label">Status</div>
                <div class="document-value">{{ ucfirst($transaction->is_retained ? 'Pendente (Retido)' : $transaction->status) }}</div>
            </div>
        </div>
        
    </div>
</body>
</html>

