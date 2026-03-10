<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovante de Pagamento - <?php echo e($transaction->transaction_id); ?></title>
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
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #000;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #000;
        }
        
        .header .status {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }
        
        .amount {
            text-align: center;
            font-size: 36px;
            font-weight: bold;
            margin: 30px 0;
            color: #000;
        }
        
        .section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
            text-transform: uppercase;
        }
        
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .row:last-child {
            border-bottom: none;
        }
        
        .label {
            font-weight: bold;
            color: #333;
            width: 200px;
        }
        
        .value {
            color: #000;
            flex: 1;
            text-align: right;
        }
        
        .document-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #000;
        }
        
        .document-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding: 6px 0;
        }
        
        .document-label {
            font-weight: bold;
            color: #333;
            width: 250px;
        }
        
        .document-value {
            color: #000;
            flex: 1;
            text-align: right;
            word-break: break-all;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #000;
            text-align: center;
            font-size: 12px;
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
            <?php if($transaction->paid_at): ?>
            <div class="status">Pago em <?php echo e($transaction->paid_at->format('d/m/Y H:i')); ?></div>
            <?php elseif($transaction->status === 'pending'): ?>
            <div class="status">Pendente</div>
            <?php else: ?>
            <div class="status"><?php echo e(ucfirst($transaction->status)); ?></div>
            <?php endif; ?>
        </div>
        
        <!-- Amount -->
        <div class="amount">
            R$ <?php echo e(number_format($transaction->amount, 2, ',', '.')); ?>

        </div>
        
        <!-- PAGADOR -->
        <div class="section">
            <div class="section-title">PAGADOR</div>
            
            <div class="row">
                <div class="label">Nome</div>
                <div class="value"><?php echo e($transaction->customer_data['name'] ?? 'N/A'); ?></div>
            </div>
            
            <div class="row">
                <div class="label">CPF/CNPJ</div>
                <div class="value"><?php echo e($customerDocument ?: 'N/A'); ?></div>
            </div>
            
            <div class="row">
                <div class="label">Email</div>
                <div class="value"><?php echo e($transaction->customer_data['email'] ?? 'N/A'); ?></div>
            </div>
            
            <?php if(isset($transaction->customer_data['phone']) && $transaction->customer_data['phone']): ?>
            <div class="row">
                <div class="label">Telefone</div>
                <div class="value"><?php echo e($transaction->customer_data['phone']); ?></div>
            </div>
            <?php endif; ?>
            
            <?php if($customerAddress): ?>
            <div class="row">
                <div class="label">Endereço</div>
                <div class="value"><?php echo e($customerAddress['street'] ?? ''); ?>, <?php echo e($customerAddress['number'] ?? ''); ?></div>
            </div>
            
            <?php if(isset($customerAddress['complement']) && $customerAddress['complement']): ?>
            <div class="row">
                <div class="label">Complemento</div>
                <div class="value"><?php echo e($customerAddress['complement']); ?></div>
            </div>
            <?php else: ?>
            <div class="row">
                <div class="label">Complemento</div>
                <div class="value">Sem complemento</div>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="label">Bairro</div>
                <div class="value"><?php echo e($customerAddress['neighborhood'] ?? ''); ?></div>
            </div>
            
            <div class="row">
                <div class="label">CEP</div>
                <div class="value"><?php echo e($customerAddress['zipcode'] ?? ''); ?></div>
            </div>
            
            <div class="row">
                <div class="label">Cidade/UF</div>
                <div class="value"><?php echo e($customerAddress['city'] ?? ''); ?>/<?php echo e($customerAddress['state'] ?? ''); ?></div>
            </div>
            <?php else: ?>
            <div class="row">
                <div class="label">Endereço</div>
                <div class="value">Não informado</div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- FAVORECIDO -->
        <div class="section">
            <div class="section-title">FAVORECIDO</div>
            
            <div class="row">
                <div class="label">Nome</div>
                <div class="value"><?php echo e($user->name); ?></div>
            </div>
            
            <?php if($user->isPessoaJuridica()): ?>
            <div class="row">
                <div class="label">CNPJ</div>
                <div class="value"><?php echo e($favoredDocument ?: 'N/A'); ?></div>
            </div>
            <?php else: ?>
            <div class="row">
                <div class="label">CPF</div>
                <div class="value"><?php echo e($favoredDocument ?: 'N/A'); ?></div>
            </div>
            <?php endif; ?>
            
            <?php if($favoredPhone): ?>
            <div class="row">
                <div class="label">Telefone</div>
                <div class="value"><?php echo e($favoredPhone); ?></div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- DOCUMENTO -->
        <div class="section document-section">
            <div class="section-title">DOCUMENTO</div>
            
            <div class="document-row">
                <div class="document-label">ID da Transação</div>
                <div class="document-value"><?php echo e($transaction->transaction_id); ?></div>
            </div>
            
            <?php if($transaction->payment_method === 'pix' && $pixPayload): ?>
            <div class="document-row">
                <div class="document-label">Chave PIX</div>
                <div class="document-value"><?php echo e($pixPayload); ?></div>
            </div>
            <?php endif; ?>
            
            <div class="document-row">
                <div class="document-label">ID End to End</div>
                <div class="document-value"><?php echo e($endToEndId ?: '-'); ?></div>
            </div>
            
            <div class="document-row">
                <div class="document-label">Código de Autenticação</div>
                <div class="document-value"><?php echo e($authCode ?: '-'); ?></div>
            </div>
            
            <?php if($transaction->external_id): ?>
            <div class="document-row">
                <div class="document-label">Referência Externa</div>
                <div class="document-value"><?php echo e($transaction->external_id); ?></div>
            </div>
            <?php endif; ?>
            
            <div class="document-row">
                <div class="document-label">Data de Criação</div>
                <div class="document-value"><?php echo e($transaction->created_at->format('d/m/Y H:i')); ?></div>
            </div>
            
            <?php if($transaction->paid_at): ?>
            <div class="document-row">
                <div class="document-label">Data de Pagamento</div>
                <div class="document-value"><?php echo e($transaction->paid_at->format('d/m/Y H:i')); ?></div>
            </div>
            <?php endif; ?>
            
            <?php if($transaction->expires_at): ?>
            <div class="document-row">
                <div class="document-label">Data de Expiração</div>
                <div class="document-value"><?php echo e($transaction->expires_at->format('d/m/Y H:i')); ?></div>
            </div>
            <?php endif; ?>
            
            <div class="document-row">
                <div class="document-label">Método de Pagamento</div>
                <div class="document-value"><?php echo e(strtoupper($transaction->payment_method === 'pix' ? 'PIX' : ($transaction->payment_method === 'credit_card' ? 'Cartão de Crédito' : 'Boleto'))); ?></div>
            </div>
            
            <div class="document-row">
                <div class="document-label">Valor Bruto</div>
                <div class="document-value">R$ <?php echo e(number_format($transaction->amount, 2, ',', '.')); ?></div>
            </div>
            
            <div class="document-row">
                <div class="document-label">Taxa</div>
                <div class="document-value">R$ <?php echo e(number_format($transaction->fee_amount, 2, ',', '.')); ?></div>
            </div>
            
            <div class="document-row">
                <div class="document-label">Valor Líquido</div>
                <div class="document-value">R$ <?php echo e(number_format($transaction->net_amount, 2, ',', '.')); ?></div>
            </div>
            
            <div class="document-row">
                <div class="document-label">Status</div>
                <div class="document-value"><?php echo e(ucfirst($transaction->is_retained ? 'Pendente (Retido)' : $transaction->status)); ?></div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>Este é um comprovante de pagamento gerado automaticamente.</p>
            <p>Gerado em <?php echo e(now()->format('d/m/Y H:i:s')); ?></p>
        </div>
    </div>
</body>
</html>

<?php /**PATH C:\xampp\htdocs\resources\views/transactions/receipt.blade.php ENDPATH**/ ?>