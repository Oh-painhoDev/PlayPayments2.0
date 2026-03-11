# 📡 Webhooks e Postbacks - PodPay

**Versão:** 1.0  
**Última Atualização:** 2025-01-20

---

## 📡 Formato dos Postbacks

Quando uma transação é criada, é possível fornecer uma URL no campo `postbackUrl` para receber notificações em seu servidor sempre que houver uma atualização na transação. O payload enviado para essa URL seguirá o formato descrito abaixo.

**⚠️ IMPORTANTE:**
- O webhook da **PodPay** será enviado para: `https://app.playpayments.com/webhook/podpay`
- Você pode fornecer um `postbackUrl` personalizado ao criar uma transação
- Os webhooks são enviados via **POST** ou **GET**
- Recomendamos que seu endpoint retorne **200 OK** para confirmar o recebimento

---

### 🔄 URL do Webhook PodPay

**Produção:**
```
https://app.playpayments.com/webhook/podpay
```

**Desenvolvimento:**
```
http://localhost:8000/webhook/podpay
```

---

### 📦 Estrutura do Payload

O webhook sempre enviará um payload com a seguinte estrutura base:

```json
{
  "type": "transaction",
  "url": "https://webhook.exemplo.com",
  "objectId": "123456",
  "data": {
    // Dados completos da transação ou transferência
  }
}
```

---

### 💳 Transação

A entidade **Transação** representa a movimentação financeira para processar pagamentos na plataforma.

#### Atributos da Transação

| Atributo | Descrição |
|----------|-----------|
| `paymentMethod` | Método de pagamento da transação:<br>- `pix`: via Pix<br>- `boleto`: via boleto bancário<br>- `credit_card`: via cartão de crédito |
| `status` | Estado atual da transação:<br>- `waiting_payment`: Aguardando pagamento<br>- `pending`: Em processo de confirmação<br>- `approved`: Pagamento aprovado<br>- `refused`: Pagamento recusado<br>- `in_protest`: Em contestação<br>- `refunded`: Pagamento reembolsado<br>- `paid`: Pagamento confirmado ✅<br>- `cancelled`: Transação cancelada<br>- `chargeback`: Estorno realizado |

Esses atributos garantem o monitoramento detalhado de cada transação, indicando o método e status do pagamento em tempo real.

---

### 🔵 Exemplo de Transação - PIX

```json
{
  "type": "transaction",
  "url": "https://webhook.exemplo.com",
  "objectId": "123456",
  "data": {
    "id": 123456,
    "tenantId": "abcd1234-5678-90ab-cdef-1234567890ab",
    "companyId": 99,
    "amount": 750,
    "currency": "BRL",
    "paymentMethod": "pix",
    "status": "waiting_payment",
    "installments": 1,
    "paidAt": null,
    "paidAmount": 0,
    "refundedAt": null,
    "refundedAmount": 0,
    "postbackUrl": "https://webhook.exemplo.com",
    "metadata": "{ \"orderId\": 123 }",
    "ip": "2001:0db8:85a3:0000:0000:8a2e:0370:7334",
    "externalRef": "pedido-abc123",
    "secureId": "fake-secure-id-0001",
    "secureUrl": "https://pagamento.exemplo.com/pagar/fake-secure-id-0001",
    "createdAt": "2025-04-29T10:00:00.000Z",
    "updatedAt": "2025-04-29T10:00:00.000Z",
    "payer": null,
    "traceable": false,
    "authorizationCode": null,
    "basePrice": 750,
    "interestRate": 0,
    "items": [
      {
        "title": "Produto Teste Fictício",
        "quantity": 1,
        "tangible": true,
        "unitPrice": 750,
        "externalRef": "item-test-001"
      }
    ],
    "customer": {
      "id": 789,
      "name": "Carlos Exemplar",
      "email": "carlos.exemplar@exemplo.com",
      "phone": "11987654321",
      "birthdate": "1995-08-20",
      "createdAt": "2025-01-01T12:00:00.000Z",
      "externalRef": null,
      "document": {
        "type": "cpf",
        "number": "00011122233"
      },
      "address": {
        "street": "Rua Fictícia",
        "streetNumber": "100",
        "complement": "Apto 10",
        "zipCode": "12345678",
        "neighborhood": "Bairro Teste",
        "city": "Cidade Exemplo",
        "state": "EX",
        "country": "BR"
      }
    },
    "fee": {
      "netAmount": 738,
      "estimatedFee": 12,
      "fixedAmount": 12,
      "spreadPercent": 1,
      "currency": "BRL"
    },
    "splits": [
      {
        "amount": 750,
        "netAmount": 738,
        "recipientId": 999,
        "chargeProcessingFee": false
      }
    ],
    "refunds": [],
    "pix": {
      "qrcode": "00020101021226870014br.gov.bcb.pix2569pix.pagamento.exemplo.com/pix/v2/abc1234504000053039865802BR5909Carlos EX6008EXEMPLO62070503***6304FAKE",
      "end2EndId": null,
      "receiptUrl": null,
      "expirationDate": "2025-04-30"
    },
    "boleto": null,
    "card": null,
    "refusedReason": null,
    "shipping": null,
    "delivery": null,
    "threeDS": {
      "redirectUrl": "https://minhaapi.com/redirect",
      "returnUrl": "https://minhaapi.com/return",
      "token": "jkhasdJHKHJKUASJKHhjksadhjkjkhHJjsfhd43ASasdfuih23jkKHBASVLdasfkjlh43"
    }
  }
}
```

---

### 💳 Exemplo de Transação - Cartão de Crédito

```json
{
  "id": 123456,
  "type": "transaction",
  "objectId": "999",
  "url": "https://minhaapi.com/retorno",
  "data": {
    "id": 999,
    "amount": 15000,
    "refundedAmount": 0,
    "companyId": 99,
    "installments": 3,
    "paymentMethod": "credit_card",
    "status": "paid",
    "postbackUrl": "https://minhaapi.com/webhook",
    "metadata": "{ \"orderId\": 123 }",
    "traceable": true,
    "secureId": "abc12345-def6-7890-ghij-klmnopqrstuv",
    "secureUrl": "https://pagamento.ficticio.com.br/pagar/abc12345-def6-7890-ghij-klmnopqrstuv",
    "createdAt": "2025-04-20T10:00:00.000Z",
    "updatedAt": "2025-04-20T10:01:00.000Z",
    "paidAt": "2025-04-20T10:02:00.000Z",
    "ip": "192.0.2.1",
    "externalRef": "trans-xyz-001",
    "customer": {
      "id": 50,
      "externalRef": "cliente-xyz",
      "name": "João da Silva",
      "email": "joao@emailficticio.com",
      "phone": "11912345678",
      "birthdate": "1990-05-10",
      "createdAt": "2025-01-01T12:00:00.000Z",
      "document": {
        "number": "98765432100",
        "type": "cpf"
      },
      "address": {
        "street": "Av. Exemplo",
        "streetNumber": "123",
        "complement": "Bloco B",
        "zipCode": "01001000",
        "neighborhood": "Centro",
        "city": "São Paulo",
        "state": "SP",
        "country": "BR"
      }
    },
    "card": {
      "id": 88,
      "brand": "mastercard",
      "holderName": "JOÃO DA SILVA",
      "lastDigits": "4321",
      "expirationMonth": 12,
      "expirationYear": 2030,
      "reusable": false,
      "createdAt": "2025-04-19T15:00:00.000Z"
    },
    "boleto": null,
    "pix": null,
    "shipping": null,
    "refusedReason": null,
    "items": [
      {
        "externalRef": "item-abc-01",
        "title": "Camisa Personalizada",
        "unitPrice": 15000,
        "quantity": 1,
        "tangible": true
      }
    ],
    "splits": [
      {
        "recipientId": 10,
        "amount": 15000,
        "netAmount": 14100
      }
    ],
    "refunds": [],
    "delivery": null,
    "fee": {
      "fixedAmount": 300,
      "spreadPercentage": 4,
      "estimatedFee": 600,
      "netAmount": 14100
    },
    "threeDS": {
      "redirectUrl": "https://minhaapi.com/redirect",
      "returnUrl": "https://minhaapi.com/return",
      "token": "jkhasdJHKHJKUASJKHhjksadhjkjkhHJjsfhd43ASasdfuih23jkKHBASVLdasfkjlh43"
    }
  }
}
```

---

### 📄 Exemplo de Transação - Boleto

```json
{
  "type": "transaction",
  "url": "https://webhook.exemplo.com/retorno",
  "objectId": "123457",
  "data": {
    "id": 123457,
    "tenantId": "abcd1111-2222-3333-4444-555566667777",
    "companyId": 99,
    "amount": 890,
    "currency": "BRL",
    "paymentMethod": "boleto",
    "status": "waiting_payment",
    "installments": 1,
    "paidAt": null,
    "paidAmount": 0,
    "refundedAt": null,
    "refundedAmount": 0,
    "postbackUrl": "https://webhook.exemplo.com/retorno",
    "returnUrl": "https://minhaapi.com/return",
    "redirectUrl": "https://minhaapi.com/redirect",
    "metadata": "{ \"orderId\": 123 }",
    "ip": "198.51.100.10",
    "externalRef": "pedido-xyz-002",
    "secureId": "fake-secure-id-0002",
    "secureUrl": "https://pagamento.exemplo.com/pagar/fake-secure-id-0002",
    "createdAt": "2025-04-29T16:00:00.000Z",
    "updatedAt": "2025-04-29T16:00:00.000Z",
    "payer": null,
    "traceable": false,
    "authorizationCode": null,
    "basePrice": null,
    "interestRate": null,
    "items": [
      {
        "title": "Assinatura Premium",
        "quantity": 1,
        "tangible": false,
        "unitPrice": 890,
        "externalRef": "item-premium-001"
      }
    ],
    "customer": {
      "id": 101,
      "name": "Ana Teste da Silva",
      "email": "ana.silva@exemplo.com",
      "phone": "11991234567",
      "birthdate": "1988-04-10",
      "createdAt": "2025-01-10T10:30:00.000Z",
      "externalRef": null,
      "document": {
        "type": "cpf",
        "number": "00011122233"
      },
      "address": {
        "street": "Av. Fictícia",
        "streetNumber": "500",
        "complement": "Apto 101",
        "zipCode": "01234567",
        "neighborhood": "Bairro Legal",
        "city": "São Paulo",
        "state": "SP",
        "country": "BR"
      }
    },
    "fee": {
      "netAmount": 880,
      "estimatedFee": 10,
      "fixedAmount": 10,
      "spreadPercent": 1,
      "currency": "BRL"
    },
    "splits": [
      {
        "amount": 890,
        "netAmount": 880,
        "recipientId": 888,
        "chargeProcessingFee": false
      }
    ],
    "refunds": [],
    "pix": null,
    "boleto": {
      "url": "https://pagamentos.exemplo.com/boletos/123457.pdf",
      "barcode": "https://pagamentos.exemplo.com/boletos/123457/barcode",
      "digitableLine": "23790123000000008901234560000000012345670000",
      "instructions": "Pagar até o vencimento. Após, sujeito a juros.",
      "expirationDate": "2025-05-02"
    },
    "card": null,
    "refusedReason": null,
    "shipping": {
      "fee": 0,
      "address": {
        "street": "Av. Fictícia",
        "streetNumber": "500",
        "complement": "Apto 101",
        "neighborhood": "Bairro Legal",
        "zipCode": "01234567",
        "city": "São Paulo",
        "state": "SP",
        "country": "BR"
      }
    },
    "delivery": {
      "status": "waiting",
      "trackingCode": null,
      "createdAt": "2025-04-29T16:00:00.000Z",
      "updatedAt": "2025-04-29T16:00:00.000Z"
    },
    "threeDS": {
      "redirectUrl": "https://minhaapi.com/redirect",
      "returnUrl": "https://minhaapi.com/return",
      "token": "jkhasdJHKHJKUASJKHhjksadhjkjkhHJjsfhd43ASasdfuih23jkKHBASVLdasfkjlh43"
    }
  }
}
```

---

### 💸 Transferência (PIX OUT)

A entidade **Transferência** representa o envio de fundos entre contas, especialmente para transações via Pix.

#### Atributos da Transferência

| Atributo | Descrição |
|----------|-----------|
| `status` | Estado atual da transferência:<br>- `COMPLETED`: concluída com sucesso ✅<br>- `PROCESSING`: em processo de execução<br>- `CANCELLED`: cancelada<br>- `REFUSED`: recusada<br>- `PENDING_ANALYSIS`: em análise<br>- `PENDING_QUEUE`: na fila de processamento |
| `pixKeyType` | Tipo de chave Pix utilizada:<br>- `cpf`: Chave vinculada ao CPF<br>- `cnpj`: Chave vinculada ao CNPJ<br>- `email`: Chave vinculada a um e-mail<br>- `phone`: Chave vinculada a um número de telefone<br>- `evp`: Chave aleatória (EVP)<br>- `copypaste`: Chave copia e cola |

Esses atributos permitem o acompanhamento detalhado do status da transferência e o tipo de chave Pix utilizada.

#### Exemplo de Transferência

```json
{
  "id": 999,
  "type": "withdraw",
  "objectId": "999",
  "url": "https://webhook.exemplo.com/retorno",
  "data": {
    "id": 999,
    "tenantId": "abcd1234-ef56-7890-abcd-1234567890ef",
    "companyId": 77,
    "amount": 500,
    "netAmount": 450,
    "fee": 50,
    "currency": "BRL",
    "method": "fiat",
    "status": "COMPLETED",
    "externalRef": null,
    "isExternal": true,
    "pixKey": "00011122233",
    "pixKeyType": "cpf",
    "pixEnd2EndId": null,
    "cryptoWallet": null,
    "cryptoNetwork": null,
    "cryptoAddress": null,
    "description": "Pagamento efetuado",
    "metadata": "{\"notaFiscal\":\"NF-0001\"}",
    "postbackUrl": "https://webhook.exemplo.com/retorno",
    "history": [],
    "transferredAt": "2025-04-28T15:30:45.000Z",
    "processedAt": "2025-04-28T15:30:30.000Z",
    "canceledAt": null,
    "createdAt": "2025-04-28T15:30:00.000Z",
    "updatedAt": "2025-04-28T15:30:45.001Z"
  }
}
```

---

### 🔧 Como Configurar seu Webhook

#### 1. Durante a Criação da Transação

Você pode fornecer um `postbackUrl` personalizado ao criar uma transação:

```json
{
  "amount": 50.00,
  "payment_method": "pix",
  "customer": {
    "name": "João Silva",
    "email": "joao@example.com",
    "document": "12345678900"
  },
  "postbackUrl": "https://seusite.com/webhook/podpay"
}
```

#### 2. Endpoint do seu Servidor

Seu endpoint deve:

- Aceitar requisições **POST** ou **GET**
- Retornar **200 OK** para confirmar o recebimento
- Processar o payload e atualizar o status da transação no seu sistema
- Ser **HTTPS** em produção (recomendado)

#### 3. Exemplo de Implementação (PHP)

```php
<?php
// webhook/podpay.php

header('Content-Type: application/json');

$payload = json_decode(file_get_contents('php://input'), true);

// Validar estrutura do payload
if (!isset($payload['type']) || !isset($payload['data'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload inválido']);
    exit;
}

$type = $payload['type'];
$data = $payload['data'];

// Processar baseado no tipo
if ($type === 'transaction') {
    $transactionId = $data['id'];
    $status = $data['status'];
    $externalRef = $data['externalRef'] ?? null;
    
    // Atualizar status da transação no seu sistema
    updateTransactionStatus($transactionId, $status, $externalRef);
    
} elseif ($type === 'withdraw') {
    $withdrawalId = $data['id'];
    $status = $data['status'];
    
    // Atualizar status do saque no seu sistema
    updateWithdrawalStatus($withdrawalId, $status);
}

// Sempre retornar 200 OK
http_response_code(200);
echo json_encode(['success' => true]);
```

#### 4. Exemplo de Implementação (Node.js)

```javascript
// webhook/podpay.js

const express = require('express');
const app = express();

app.use(express.json());

app.post('/webhook/podpay', (req, res) => {
  const payload = req.body;
  
  // Validar estrutura
  if (!payload.type || !payload.data) {
    return res.status(400).json({ error: 'Payload inválido' });
  }
  
  const { type, data } = payload;
  
  // Processar baseado no tipo
  if (type === 'transaction') {
    const { id, status, externalRef } = data;
    // Atualizar status da transação no seu sistema
    updateTransactionStatus(id, status, externalRef);
    
  } else if (type === 'withdraw') {
    const { id, status } = data;
    // Atualizar status do saque no seu sistema
    updateWithdrawalStatus(id, status);
  }
  
  // Sempre retornar 200 OK
  res.status(200).json({ success: true });
});

app.listen(3000);
```

---

### ⚠️ Segurança

1. **Validação de IP:** Considere validar o IP de origem (PodPay)
2. **Assinatura:** Verifique se há assinatura HMAC no header (se disponível)
3. **HTTPS:** Sempre use HTTPS em produção
4. **Idempotência:** Processar webhooks de forma idempotente (evitar duplicação)
5. **Timeout:** Configure timeout adequado no seu servidor (recomendado: 10 segundos)

---

### 📝 Notas Importantes

- O webhook pode ser enviado múltiplas vezes para garantir entrega
- Sempre verifique o `status` atual antes de processar
- Use `externalRef` para identificar transações no seu sistema
- O campo `id` é o ID interno da PodPay
- Para PIX, verifique `pix.end2EndId` quando o pagamento for confirmado

---

## 🔍 Buscar Transação

### **GET** `https://api.podpay.co/v1/transactions/{id}`

A rota `GET /transactions/{id}` permite recuperar os detalhes de uma venda específica por meio do identificador único da transação (`id`). Essa rota é útil para obter informações detalhadas sobre uma transação previamente criada.

#### Path Params

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `id` | int32 | ID da transação |

#### Resposta de Sucesso (200)

```json
{
  "id": 123456,
  "amount": 15000,
  "refundedAmount": 0,
  "companyId": 99,
  "installments": 3,
  "paymentMethod": "credit_card",
  "status": "paid",
  "postbackUrl": "https://minhaapi.com/webhook",
  "metadata": "{ \"orderId\": 123 }",
  "traceable": true,
  "secureId": "abc12345-def6-7890-ghij-klmnopqrstuv",
  "secureUrl": "https://pagamento.ficticio.com.br/pagar/abc12345-def6-7890-ghij-klmnopqrstuv",
  "createdAt": "2025-04-20T10:00:00.000Z",
  "updatedAt": "2025-04-20T10:01:00.000Z",
  "paidAt": "2025-04-20T10:02:00.000Z",
  "ip": "192.0.2.1",
  "externalRef": "trans-xyz-001",
  "customer": {
    "id": 50,
    "name": "João da Silva",
    "email": "joao@emailficticio.com"
  },
  "card": {
    "id": 88,
    "brand": "mastercard",
    "lastDigits": "4321"
  },
  "boleto": null,
  "pix": null,
  "items": []
}
```

#### Resposta de Erro (400)

```json
{
  "error": "Transação não encontrada"
}
```

---

## 📞 Suporte

- **Documentação:** https://api.podpay.co/docs
- **Status:** https://status.podpay.co

---

**🎉 Documentação de Webhooks - PodPay**

**Versão:** 1.0  
**Última Atualização:** 2025-01-20


