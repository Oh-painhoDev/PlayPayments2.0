# API v1/transactions - Documentação Completa

## 🔐 Autenticação

### Para Criar Transação (POST) - Requer AMBOS os Tokens

Para criar uma transação PIX, você **DEVE** fornecer **AMBOS** os tokens:
- **Public Key** (PB-playpayments-...)
- **Private Key** (SK-playpayments-...) também conhecida como Secret Key

### Para Consultar Transação (GET) - Aceita Um Token

Para consultar transações, você pode usar apenas um dos tokens:
- **Public Key** (PB-playpayments-...) - somente leitura
- **Private Key** (SK-playpayments-...) - leitura e escrita

## 📍 Endpoints

### 1. Criar Venda (Gerar PIX)

**POST** `/api/v1/transactions`

#### Headers Obrigatórios

```
X-Public-Key: PB-playpayments-seu-public-key-aqui
X-Private-Key: SK-playpayments-seu-private-key-aqui
Content-Type: application/json
```

**OU** usando Authorization Bearer:

```
Authorization: Bearer PB-playpayments-seu-public-key-aqui:SK-playpayments-seu-private-key-aqui
Content-Type: application/json
```

#### Body (JSON)

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
  "description": "Pagamento de assinatura",
  "external_id": "PEDIDO_2024_001",
  "pix_expires_in_minutes": 30
}
```

#### Parâmetros

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `amount` | number | Sim | Valor da transação (mínimo 0.01) |
| `payment_method` | string | Sim | Método de pagamento: `pix`, `credit_card`, `bank_slip` |
| `customer.name` | string | Sim | Nome do cliente |
| `customer.email` | string | Sim | Email do cliente |
| `customer.document` | string | Sim | CPF/CNPJ do cliente (apenas números) |
| `customer.phone` | string | Não | Telefone do cliente (apenas números) |
| `description` | string | Não | Descrição da transação |
| `external_id` | string | Não | ID externo único da transação |
| `pix_expires_in_minutes` | integer | Não | Tempo de expiração do PIX em minutos (padrão: 15) |
| `expires_in` | integer | Não | Tempo de expiração em segundos (alternativa ao pix_expires_in_minutes) |

#### Resposta de Sucesso (201)

```json
{
  "success": true,
  "data": {
    "id": "TXN_ABC123XYZ",
    "transaction_id": "TXN_ABC123XYZ",
    "external_id": "PEDIDO_2024_001",
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
    "description": "Pagamento de assinatura",
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

#### Resposta de Erro (400/422)

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

---

### 2. Buscar Venda (Consultar Status)

**GET** `/api/v1/transactions/{id}`

#### Headers

```
Authorization: Bearer SK-playpayments-seu-private-key-aqui
```

**OU**

```
Authorization: Bearer PB-playpayments-seu-public-key-aqui
```

#### Parâmetros da URL

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `id` | string | ID da transação (`transaction_id` ou `external_id`) |

#### Resposta de Sucesso (200)

```json
{
  "success": true,
  "data": {
    "id": "TXN_ABC123XYZ",
    "transaction_id": "TXN_ABC123XYZ",
    "external_id": "PEDIDO_2024_001",
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
    "description": "Pagamento de assinatura",
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

#### Resposta de Erro (404)

```json
{
  "success": false,
  "error": "Transação não encontrada"
}
```

---

## 📊 Status da Transação

Os possíveis valores de `status` são:

- `pending` - Aguardando pagamento
- `processing` - Processando
- `paid` - Pago
- `expired` - Expirado
- `cancelled` - Cancelado
- `failed` - Falhou
- `refunded` - Estornado

---

## 💡 Exemplos de Uso

### Exemplo 1: Criar PIX com cURL

```bash
curl -X POST "https://seu-dominio.com/api/v1/transactions" \
  -H "X-Public-Key: PB-playpayments-seu-public-key-aqui" \
  -H "X-Private-Key: SK-playpayments-seu-private-key-aqui" \
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
    "description": "Pagamento de assinatura",
    "external_id": "PEDIDO_2024_001",
    "pix_expires_in_minutes": 30
  }'
```

### Exemplo 2: Consultar Status com cURL

```bash
curl -X GET "https://seu-dominio.com/api/v1/transactions/TXN_ABC123XYZ" \
  -H "Authorization: Bearer SK-playpayments-seu-private-key-aqui"
```

### Exemplo 3: JavaScript (Fetch API)

```javascript
// Criar PIX
async function criarPix() {
  const response = await fetch('https://seu-dominio.com/api/v1/transactions', {
    method: 'POST',
    headers: {
      'X-Public-Key': 'PB-playpayments-seu-public-key-aqui',
      'X-Private-Key': 'SK-playpayments-seu-private-key-aqui',
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
      description: 'Pagamento de assinatura',
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
  const response = await fetch(`https://seu-dominio.com/api/v1/transactions/${transactionId}`, {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer SK-playpayments-seu-private-key-aqui'
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

### Exemplo 4: PHP

```php
<?php

// Criar PIX
function criarPix() {
    $url = 'https://seu-dominio.com/api/v1/transactions';
    
    $data = [
        'amount' => 50.00,
        'payment_method' => 'pix',
        'customer' => [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'document' => '12345678900',
            'phone' => '11988887777'
        ],
        'description' => 'Pagamento de assinatura',
        'external_id' => 'PEDIDO_2024_001',
        'pix_expires_in_minutes' => 30
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Public-Key: PB-playpayments-seu-public-key-aqui',
        'X-Private-Key: SK-playpayments-seu-private-key-aqui',
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Consultar Status
function consultarStatus($transactionId) {
    $url = "https://seu-dominio.com/api/v1/transactions/{$transactionId}";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer SK-playpayments-seu-private-key-aqui'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}
```

---

## 🔄 Fluxo Completo

1. **Criar Transação**: Faça um POST para `/api/v1/transactions` com os dados do pagamento
2. **Receber QR Code**: A resposta incluirá o QR Code PIX no campo `data.pix.qr_code`
3. **Exibir QR Code**: Use o QR Code para gerar a imagem e exibir para o cliente
4. **Consultar Status**: Faça GET para `/api/v1/transactions/{id}` periodicamente para verificar se foi pago
5. **Confirmar Pagamento**: Quando `status` for `paid`, confirme o pagamento no seu sistema

---

## ⚠️ Observações Importantes

1. **CORS**: A API está configurada para aceitar requisições de outros websites
2. **Autenticação**: Para criar transações, você **DEVE** fornecer ambos os tokens (Public Key e Private Key)
3. **QR Code**: O campo `pix.qr_code` contém o código PIX completo que pode ser usado para gerar a imagem do QR Code
4. **Status**: Consulte o status periodicamente (recomendado a cada 5-10 segundos) enquanto aguarda pagamento
5. **Expiração**: O PIX expira após o tempo definido em `pix_expires_in_minutes` (padrão: 15 minutos)

---

## 🆘 Suporte

Em caso de dúvidas ou problemas, entre em contato com o suporte!








