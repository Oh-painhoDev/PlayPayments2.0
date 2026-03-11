# 📚 Documentação Completa da API - playpayments Gateway

**Base URL:** `https://api.playpayments.online` ou `https://playpayments.com/api`

**Versão:** 1.0  
**Última Atualização:** 2025-01-20

---

## 📋 Índice

1. [Autenticação](#1-autenticação)
2. [Health Check](#2-health-check)
3. [Autenticação JWT](#3-autenticação-jwt)
4. [Webhooks e Postbacks](#4-webhooks-e-postbacks)
5. [API v1 - Transações (Transactions)](#5-api-v1---transações-transactions)
6. [API v1 - Clientes (Customers)](#6-api-v1---clientes-customers)
7. [API v1 - Saques (Withdrawals)](#7-api-v1---saques-withdrawals)
8. [API v1 - Saldo (Balance)](#8-api-v1---saldo-balance)
9. [Payments API](#9-payments-api)
10. [PIX API](#10-pix-api)
11. [Withdrawals API (PIX OUT)](#11-withdrawals-api-pix-out)
12. [External PIX API](#12-external-pix-api)
13. [Utmify API](#13-utmify-api)
14. [Astrofy API](#14-astrofy-api)
15. [Test API](#15-test-api)
16. [Códigos de Erro](#16-códigos-de-erro)
17. [Exemplos Completos](#17-exemplos-completos)

---

## 1. Autenticação

### 🔑 Tipos de Autenticação

A API playpayments suporta dois tipos de autenticação:

#### 1.1. API Keys (Public Key + Private Key)

**Public Key (PB-playpayments-...):**
- Usado para operações de leitura (GET)
- Pode ser usado sozinho para consultar transações

**Private Key (SK-playpayments-...):**
- Usado para operações de escrita (POST, PUT, DELETE)
- Também pode ser usado para leitura

**⚠️ IMPORTANTE:**
- Para criar transações PIX (`POST /v1/transactions`), você **DEVE** fornecer **AMBOS** os tokens (Public Key + Private Key)
- Para consultar transações (`GET /v1/transactions`), você pode usar apenas um dos tokens

---

### 📤 Enviando as Chaves

#### Opção 1: Headers (Recomendado)

```http
X-Public-Key: PB-playpayments-sua-chave-publica
X-Private-Key: SK-playpayments-sua-chave-secreta
```

#### Opção 2: Authorization Bearer

```http
Authorization: Bearer SK-playpayments-sua-chave-secreta
```

**OU** para criar transações (com Public + Private):

```http
Authorization: Bearer PB-playpayments-public-key:SK-playpayments-private-key
```

---

## 2. Health Check

### Verificar Status da API

**GET** `/health`

**Autenticação:** Não requerida

**Resposta:**

```json
{
  "status": "ok",
  "service": "API Subdomain - api.playpayments.com",
  "timestamp": "2025-01-20T10:00:00.000000Z"
}
```

**Exemplo cURL:**

```bash
curl -X GET https://api.playpayments.online/health
```

---

## 3. Autenticação JWT

### 3.1. Login

**POST** `/auth/login`

**Autenticação:** Não requerida

**Body:**

```json
{
  "email": "usuario@example.com",
  "password": "senha123"
}
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
      "id": 1,
      "name": "João Silva",
      "email": "usuario@example.com"
    }
  }
}
```

**Resposta de Erro (401):**

```json
{
  "success": false,
  "error": "Credenciais inválidas"
}
```

---

### 3.2. Refresh Token

**POST** `/auth/refresh`

**Autenticação:** Não requerida

**Headers:**

```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

---

### 3.3. Logout

**POST** `/auth/logout`

**Autenticação:** JWT Token

**Headers:**

```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "message": "Logout realizado com sucesso"
}
```

---

### 3.4. Dados do Usuário

**GET** `/auth/me`

**Autenticação:** JWT Token

**Headers:**

```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "João Silva",
    "email": "usuario@example.com",
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

## 4. Webhooks e Postbacks

### 📡 Formato dos Postbacks

Quando uma transação é criada, é possível fornecer uma URL no campo `postbackUrl` para receber notificações em seu servidor sempre que houver uma atualização na transação. O payload enviado para essa URL seguirá o formato descrito abaixo.

**⚠️ IMPORTANTE:**
- O webhook da **ShieldTech** será enviado para: `https://app.playpayments.com/webhook/shieldtech`
- Você pode fornecer um `postbackUrl` personalizado ao criar uma transação
- Os webhooks são enviados via **POST** ou **GET**
- Recomendamos que seu endpoint retorne **200 OK** para confirmar o recebimento

---

### 🔄 URL do Webhook ShieldTech

**Produção:**
```
https://app.playpayments.com/webhook/shieldtech
```

**Desenvolvimento:**
```
http://localhost:8000/webhook/shieldtech
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
  "postbackUrl": "https://seusite.com/webhook/shieldtech"
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
// webhook/shieldtech.php

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
// webhook/shieldtech.js

const express = require('express');
const app = express();

app.use(express.json());

app.post('/webhook/shieldtech', (req, res) => {
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

1. **Validação de IP:** Considere validar o IP de origem (ShieldTech)
2. **Assinatura:** Verifique se há assinatura HMAC no header (se disponível)
3. **HTTPS:** Sempre use HTTPS em produção
4. **Idempotência:** Processar webhooks de forma idempotente (evitar duplicação)
5. **Timeout:** Configure timeout adequado no seu servidor (recomendado: 10 segundos)

---

### 📝 Notas Importantes

- O webhook pode ser enviado múltiplas vezes para garantir entrega
- Sempre verifique o `status` atual antes de processar
- Use `externalRef` para identificar transações no seu sistema
- O campo `id` é o ID interno da ShieldTech
- Para PIX, verifique `pix.end2EndId` quando o pagamento for confirmado

---

## 5. API v1 - Transações (Transactions)

### 5.1. Criar Transação PIX

**POST** `/v1/transactions`

**Autenticação:** Public Key + Private Key (ambos obrigatórios)

**Headers:**

```http
X-Public-Key: PB-playpayments-sua-chave-publica
X-Private-Key: SK-playpayments-sua-chave-secreta
Content-Type: application/json
```

**Body:**

```json
{
  "amount": 50.00,
  "payment_method": "pix",
  "customer": {
    "name": "João Silva",
    "email": "joao@example.com",
    "document": "12345678900",
    "phone": "11988887777"
  },
  "description": "Pagamento de produto",
  "external_id": "PEDIDO_001",
  "pix_expires_in_minutes": 30
}
```

**Parâmetros:**

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `amount` | number | ✅ Sim | Valor da transação (mínimo 0.01) |
| `payment_method` | string | ✅ Sim | Método: `pix`, `credit_card`, `bank_slip` |
| `customer.name` | string | ✅ Sim | Nome do cliente |
| `customer.email` | string | ✅ Sim | Email do cliente |
| `customer.document` | string | ✅ Sim | CPF/CNPJ (apenas números) |
| `customer.phone` | string | ❌ Não | Telefone (apenas números) |
| `description` | string | ❌ Não | Descrição da transação |
| `external_id` | string | ❌ Não | ID externo único da transação |
| `pix_expires_in_minutes` | integer | ❌ Não | Tempo de expiração em minutos (padrão: 15) |

**Resposta de Sucesso (201):**

```json
{
  "success": true,
  "data": {
    "id": "TXN_ABC123XYZ",
    "transaction_id": "TXN_ABC123XYZ",
    "external_id": "PEDIDO_001",
    "amount": 50.00,
    "fee_amount": 0.00,
    "net_amount": 50.00,
    "currency": "BRL",
    "payment_method": "pix",
    "status": "pending",
    "is_retained": false,
    "customer": {
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900",
      "phone": "11988887777"
    },
    "pix": {
      "qr_code": "00020126580014br.gov.bcb.pix...",
      "payload": "00020126580014br.gov.bcb.pix...",
      "qrcode": "00020126580014br.gov.bcb.pix...",
      "end_to_end_id": null,
      "txid": "E12345678202401151234567890123456",
      "expiration_date": "2024-01-15T14:30:00.000000Z"
    },
    "description": "Pagamento de produto",
    "gateway": {
      "id": 1,
      "name": "E2 Bank"
    },
    "expires_at": "2024-01-15T14:30:00.000000Z",
    "paid_at": null,
    "refunded_at": null,
    "created_at": "2024-01-15T14:00:00.000000Z",
    "updated_at": "2024-01-15T14:00:00.000000Z"
  }
}
```

---

### 5.2. Listar Transações

**GET** `/v1/transactions`

**Autenticação:** Public Key ou Private Key

**Headers:**

```http
Authorization: Bearer SK-playpayments-sua-chave-secreta
```

**Query Parameters:**

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `per_page` | integer | Itens por página (padrão: 15) |
| `page` | integer | Número da página (padrão: 1) |
| `status` | string | Filtrar por status: `pending`, `paid`, `expired`, etc. |
| `payment_method` | string | Filtrar por método: `pix`, `credit_card`, etc. |
| `start_date` | date | Data inicial (YYYY-MM-DD) |
| `end_date` | date | Data final (YYYY-MM-DD) |
| `search` | string | Buscar por transaction_id, external_id, nome ou email |

**Exemplo:**

```bash
GET /v1/transactions?status=paid&per_page=20&page=1
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "data": [
    {
      "id": "TXN_ABC123XYZ",
      "transaction_id": "TXN_ABC123XYZ",
      "amount": 50.00,
      "status": "paid",
      "payment_method": "pix",
      "created_at": "2024-01-15T14:00:00.000000Z"
    }
  ],
  "pagination": {
    "total": 100,
    "per_page": 15,
    "current_page": 1,
    "last_page": 7,
    "from": 1,
    "to": 15
  }
}
```

---

### 5.3. Buscar Transação

**GET** `/v1/transactions/{id}`

**Autenticação:** Public Key ou Private Key

**Headers:**

```http
Authorization: Bearer SK-playpayments-sua-chave-secreta
```

**Parâmetros da URL:**

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `id` | string | ID da transação (`transaction_id` ou `external_id`) |

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "data": {
    "id": "TXN_ABC123XYZ",
    "transaction_id": "TXN_ABC123XYZ",
    "external_id": "PEDIDO_001",
    "amount": 50.00,
    "fee_amount": 0.00,
    "net_amount": 50.00,
    "currency": "BRL",
    "payment_method": "pix",
    "status": "paid",
    "is_retained": false,
    "customer": {
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900",
      "phone": "11988887777"
    },
    "pix": {
      "qr_code": "00020126580014br.gov.bcb.pix...",
      "payload": "00020126580014br.gov.bcb.pix...",
      "qrcode": "00020126580014br.gov.bcb.pix...",
      "end_to_end_id": "E12345678202401151234567890123456",
      "txid": "E12345678202401151234567890123456",
      "expiration_date": "2024-01-15T14:30:00.000000Z"
    },
    "description": "Pagamento de produto",
    "gateway": {
      "id": 1,
      "name": "E2 Bank"
    },
    "expires_at": "2024-01-15T14:30:00.000000Z",
    "paid_at": "2024-01-15T14:15:00.000000Z",
    "refunded_at": null,
    "created_at": "2024-01-15T14:00:00.000000Z",
    "updated_at": "2024-01-15T14:15:00.000000Z"
  }
}
```

**Resposta de Erro (404):**

```json
{
  "success": false,
  "error": "Transação não encontrada"
}
```

---

### 📊 Status da Transação

Os possíveis valores de `status` são:

- `pending` - Aguardando pagamento
- `processing` - Processando
- `paid` - Pago ✅
- `expired` - Expirado
- `cancelled` - Cancelado
- `failed` - Falhou
- `refunded` - Estornado

---

## 6. API v1 - Clientes (Customers)

### 6.1. Listar Clientes

**GET** `/v1/customers`

**Autenticação:** API Key

**Resposta:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900",
      "phone": "11988887777",
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

---

### 6.2. Criar Cliente

**POST** `/v1/customers`

**Autenticação:** API Key

**Body:**

```json
{
  "name": "João Silva",
  "email": "joao@example.com",
  "document": "12345678900",
  "phone": "11988887777"
}
```

---

### 6.3. Buscar Cliente

**GET** `/v1/customers/{id}`

**Autenticação:** API Key

---

### 6.4. Atualizar Cliente

**PUT** `/v1/customers/{id}`

**Autenticação:** API Key

**Body:**

```json
{
  "name": "João Silva Santos",
  "phone": "11999999999"
}
```

---

### 6.5. Deletar Cliente

**DELETE** `/v1/customers/{id}`

**Autenticação:** API Key

---

## 7. API v1 - Saques (Withdrawals)

### 7.1. Listar Saques

**GET** `/v1/withdrawals`

**Autenticação:** API Key

---

### 7.2. Criar Saque

**POST** `/v1/withdrawals`

**Autenticação:** API Key

**Body:**

```json
{
  "amount": 100.00,
  "pix_key": "joao@example.com",
  "pix_key_type": "email",
  "description": "Saque para conta pessoal"
}
```

---

### 7.3. Buscar Saque

**GET** `/v1/withdrawals/{id}`

**Autenticação:** API Key

---

## 8. API v1 - Saldo (Balance)

### 8.1. Consultar Saldo

**GET** `/v1/balance`

**Autenticação:** API Key

**Resposta:**

```json
{
  "success": true,
  "data": {
    "available": 1500.00,
    "pending": 200.00,
    "total": 1700.00
  }
}
```

---

## 9. Payments API

### 9.1. Criar Pagamento

**POST** `/payments`

**Autenticação:** API Key

**Body:**

```json
{
  "amount": 50.00,
  "payment_method": "pix",
  "customer": {
    "name": "João Silva",
    "email": "joao@example.com",
    "document": "12345678900"
  }
}
```

---

### 9.2. Listar Pagamentos

**GET** `/payments`

**Autenticação:** API Key

---

### 9.3. Buscar Pagamento

**GET** `/payments/{transactionId}`

**Autenticação:** API Key

---

### 9.4. Status do Pagamento

**GET** `/payments/status/{transactionId}`

**Autenticação:** API Key

---

## 10. PIX API

### 10.1. Criar PIX

**POST** `/pix`

**Autenticação:** API Key

**Body:**

```json
{
  "amount": 50.00,
  "customer": {
    "name": "João Silva",
    "email": "joao@example.com",
    "document": "12345678900"
  },
  "expires_in": 1800
}
```

---

### 10.2. Listar PIX

**GET** `/pix`

**Autenticação:** API Key

---

### 10.3. Buscar PIX

**GET** `/pix/{transactionId}`

**Autenticação:** API Key

---

### 10.4. Status do PIX

**GET** `/pix/status/{transactionId}`

**Autenticação:** API Key

---

## 11. Withdrawals API (PIX OUT)

### 11.1. Criar Saque

**POST** `/withdrawals`

**Autenticação:** API Key

**Body:**

```json
{
  "amount": 100.00,
  "pix_key": "joao@example.com",
  "pix_key_type": "email"
}
```

---

### 11.2. Listar Saques

**GET** `/withdrawals`

**Autenticação:** API Key

---

### 11.3. Buscar Saque

**GET** `/withdrawals/{withdrawalId}`

**Autenticação:** API Key

---

### 11.4. Status do Saque

**GET** `/withdrawals/status/{withdrawalId}`

**Autenticação:** API Key

---

## 12. External PIX API

Permite usar a API playpayments para criar PIX usando credenciais de outras APIs PIX.

### 12.1. Criar PIX Externo

**POST** `/external-pix/create`

**Autenticação:** API Key (para identificar a conta playpayments)

**Body:**

```json
{
  "amount": 50.00,
  "customer": {
    "name": "João Silva",
    "email": "joao@example.com",
    "document": "12345678900"
  },
  "api_url": "https://api.sharkbanking.com.br/v1/transactions",
  "api_token": "seu_token_aqui",
  "auth_type": "bearer",
  "expires_in": 1800
}
```

---

### 12.2. Status do PIX Externo

**GET** `/external-pix/status/{transactionId}`

**Autenticação:** API Key

**Query Parameters:**

- `api_url` - URL da API externa
- `api_token` - Token da API externa
- `auth_type` - Tipo de autenticação (`bearer` ou `basic`)

---

## 13. Utmify API

### 13.1. Gerar PIX e Enviar para UTMify

**POST** `/utmify/generate-pix`

**Autenticação:** API Key

**Body:**

```json
{
  "amount": 50.00,
  "customer": {
    "name": "João Silva",
    "email": "joao@example.com",
    "document": "12345678900"
  },
  "utm_source": "google",
  "utm_campaign": "promo_2024"
}
```

**⚠️ IMPORTANTE:** O token da UTMify deve estar configurado na conta do usuário.

---

## 14. Astrofy API

### 14.1. Criar Pedido

**POST** `/astrofy/order`

**Autenticação:** Não requerida (usa token próprio da Astrofy)

**Body:**

```json
{
  "external_id": "PEDIDO_001",
  "amount": 50.00,
  "customer": {
    "name": "João Silva",
    "email": "joao@example.com"
  }
}
```

---

### 14.2. Status do Pedido

**GET** `/astrofy/order/{externalId}`

**Autenticação:** Não requerida

---

## 15. Test API

### 15.1. Teste da API

**GET** `/test`

**Autenticação:** API Key

**Resposta:**

```json
{
  "success": true,
  "message": "API funcionando corretamente",
  "timestamp": "2025-01-20T10:00:00.000000Z"
}
```

---

### 15.2. Teste de PIX

**POST** `/test/pix`

**Autenticação:** API Key

**Body:**

```json
{
  "amount": 10.00
}
```

---

### 15.3. Teste Simples de PIX

**POST** `/test-pix-simple`

**Autenticação:** Não requerida (apenas desenvolvimento)

**Body:**

```json
{
  "user_id": 1,
  "amount": 10.00
}
```

---

## 16. Códigos de Erro

### 16.1. Códigos HTTP

| Código | Descrição |
|--------|-----------|
| 200 | Sucesso |
| 201 | Criado com sucesso |
| 400 | Requisição inválida |
| 401 | Não autorizado |
| 403 | Acesso negado |
| 404 | Não encontrado |
| 422 | Dados inválidos |
| 500 | Erro interno do servidor |

---

### 16.2. Resposta de Erro Padrão

```json
{
  "success": false,
  "error": "Descrição do erro",
  "errors": {
    "campo": ["Mensagem de erro do campo"]
  }
}
```

---

### 16.3. Erros Comuns

#### 401 - Não Autorizado

```json
{
  "success": false,
  "error": "Token inválido ou expirado"
}
```

**Solução:** Verifique se o token está correto e não expirou.

---

#### 422 - Dados Inválidos

```json
{
  "success": false,
  "error": "Dados inválidos",
  "errors": {
    "amount": ["O campo amount é obrigatório."],
    "customer.email": ["O campo customer.email deve ser um email válido."]
  }
}
```

**Solução:** Verifique se todos os campos obrigatórios foram preenchidos corretamente.

---

#### 400 - Gateway Não Configurado

```json
{
  "success": false,
  "error": "Gateway não configurado"
}
```

**Solução:** Configure um gateway de pagamento na sua conta.

---

## 17. Exemplos Completos

### 17.1. JavaScript (Fetch API)

```javascript
// Criar PIX
async function criarPix() {
  const response = await fetch('https://api.playpayments.online/v1/transactions', {
    method: 'POST',
    headers: {
      'X-Public-Key': 'PB-playpayments-sua-chave-publica',
      'X-Private-Key': 'SK-playpayments-sua-chave-secreta',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      amount: 50.00,
      payment_method: 'pix',
      customer: {
        name: 'João Silva',
        email: 'joao@example.com',
        document: '12345678900',
        phone: '11988887777'
      },
      description: 'Pagamento de produto',
      external_id: 'PEDIDO_2024_001',
      pix_expires_in_minutes: 30
    })
  });

  const data = await response.json();
  
  if (data.success) {
    console.log('QR Code PIX:', data.data.pix.qr_code);
    console.log('Transaction ID:', data.data.transaction_id);
  }
}

// Consultar Status
async function consultarStatus(transactionId) {
  const response = await fetch(`https://api.playpayments.online/v1/transactions/${transactionId}`, {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer SK-playpayments-sua-chave-secreta'
    }
  });

  const data = await response.json();
  
  if (data.success) {
    console.log('Status:', data.data.status);
    if (data.data.status === 'paid') {
      console.log('Pagamento confirmado!');
    }
  }
}
```

---

### 17.2. PHP (cURL)

```php
<?php

// Criar PIX
function criarPix() {
    $url = 'https://api.playpayments.online/v1/transactions';
    
    $data = [
        'amount' => 50.00,
        'payment_method' => 'pix',
        'customer' => [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'document' => '12345678900',
            'phone' => '11988887777'
        ],
        'description' => 'Pagamento de produto',
        'external_id' => 'PEDIDO_2024_001',
        'pix_expires_in_minutes' => 30
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Public-Key: PB-playpayments-sua-chave-publica',
        'X-Private-Key: SK-playpayments-sua-chave-secreta',
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Consultar Status
function consultarStatus($transactionId) {
    $url = "https://api.playpayments.online/v1/transactions/{$transactionId}";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer SK-playpayments-sua-chave-secreta'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}
```

---

### 17.3. Python (requests)

```python
import requests

# Criar PIX
def criar_pix():
    url = 'https://api.playpayments.online/v1/transactions'
    
    headers = {
        'X-Public-Key': 'PB-playpayments-sua-chave-publica',
        'X-Private-Key': 'SK-playpayments-sua-chave-secreta',
        'Content-Type': 'application/json'
    }
    
    data = {
        'amount': 50.00,
        'payment_method': 'pix',
        'customer': {
            'name': 'João Silva',
            'email': 'joao@example.com',
            'document': '12345678900',
            'phone': '11988887777'
        },
        'description': 'Pagamento de produto',
        'external_id': 'PEDIDO_2024_001',
        'pix_expires_in_minutes': 30
    }
    
    response = requests.post(url, json=data, headers=headers)
    return response.json()

# Consultar Status
def consultar_status(transaction_id):
    url = f'https://api.playpayments.online/v1/transactions/{transaction_id}'
    
    headers = {
        'Authorization': 'Bearer SK-playpayments-sua-chave-secreta'
    }
    
    response = requests.get(url, headers=headers)
    return response.json()
```

---

### 17.4. cURL

```bash
# Criar PIX
curl -X POST "https://api.playpayments.online/v1/transactions" \
  -H "X-Public-Key: PB-playpayments-sua-chave-publica" \
  -H "X-Private-Key: SK-playpayments-sua-chave-secreta" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 50.00,
    "payment_method": "pix",
    "customer": {
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900",
      "phone": "11988887777"
    },
    "description": "Pagamento de produto",
    "external_id": "PEDIDO_2024_001",
    "pix_expires_in_minutes": 30
  }'

# Consultar Status
curl -X GET "https://api.playpayments.online/v1/transactions/TXN_ABC123XYZ" \
  -H "Authorization: Bearer SK-playpayments-sua-chave-secreta"
```

---

## 📝 Notas Importantes

1. **Base URL:**
   - Produção: `https://api.playpayments.online`
   - Desenvolvimento: `http://api.playpayments.com:8000`

2. **Autenticação:**
   - Para criar transações: **AMBOS** os tokens (Public + Private)
   - Para consultar: apenas **UM** dos tokens

3. **Formatos de Data:**
   - ISO 8601: `2024-01-15T14:00:00.000000Z`

4. **Valores Monetários:**
   - Sempre em número decimal (ex: `50.00`)

5. **IDs:**
   - `transaction_id`: ID interno da transação
   - `external_id`: ID externo fornecido por você

6. **Rate Limiting:**
   - 100 requisições por minuto por token

---

## 📞 Suporte

- **Email:** suporte@playpayments.com
- **Documentação:** https://playpayments.com/api-docs
- **Status:** https://status.playpayments.com

---

**🎉 Documentação Completa da API - playpayments Gateway**

**Versão:** 1.0  
**Última Atualização:** 2025-01-20


