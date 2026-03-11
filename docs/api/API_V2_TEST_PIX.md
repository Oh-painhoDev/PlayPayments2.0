# API v2 - Test PIX

## Endpoint

**POST** `https://api.seudominio.com/v2/transactions/test-pix`

Cria um PIX de teste usando automaticamente o gateway configurado do usuário autenticado.

---

## Autenticação

Esta rota requer **ambas as chaves** (Public Key e Private Key):

- **X-Public-Key**: `PB-playpayments-...`
- **X-Private-Key**: `SK-playpayments-...`

Ou via Basic Auth:
- **Username**: Public Key
- **Password**: Private Key

---

## Body Parameters (Opcionais)

| Campo | Tipo | Descrição | Padrão |
|-------|------|-----------|--------|
| `amount` | `float` | Valor da transação em reais | `10.00` |
| `pix_expires_in_minutes` | `integer` | Tempo de expiração do PIX em minutos | `15` |
| `description` | `string` | Descrição da transação | `"PIX de Teste - API v2"` |
| `customer.name` | `string` | Nome do cliente | Dados do usuário autenticado |
| `customer.email` | `string` | E-mail do cliente | Dados do usuário autenticado |
| `customer.document` | `string` | CPF/CNPJ do cliente | Dados do usuário autenticado |
| `customer.phone` | `string` | Telefone do cliente | Dados do usuário autenticado |

---

## Exemplo de Requisição

### Exemplo 1: PIX de Teste Simples (usa valores padrão)

```bash
curl --request POST \
  --url https://api.seudominio.com/v2/transactions/test-pix \
  --header 'X-Public-Key: PB-playpayments-SUA_PUBLIC_KEY' \
  --header 'X-Private-Key: SK-playpayments-SUA_PRIVATE_KEY' \
  --header 'Content-Type: application/json'
```

### Exemplo 2: PIX de Teste com Valor Customizado

```bash
curl --request POST \
  --url https://api.seudominio.com/v2/transactions/test-pix \
  --header 'X-Public-Key: PB-playpayments-SUA_PUBLIC_KEY' \
  --header 'X-Private-Key: SK-playpayments-SUA_PRIVATE_KEY' \
  --header 'Content-Type: application/json' \
  --data '{
    "amount": 25.50,
    "pix_expires_in_minutes": 30,
    "description": "Teste de PIX personalizado"
  }'
```

### Exemplo 3: PIX de Teste com Dados do Cliente Customizados

```bash
curl --request POST \
  --url https://api.seudominio.com/v2/transactions/test-pix \
  --header 'X-Public-Key: PB-playpayments-SUA_PUBLIC_KEY' \
  --header 'X-Private-Key: SK-playpayments-SUA_PRIVATE_KEY' \
  --header 'Content-Type: application/json' \
  --data '{
    "amount": 50.00,
    "pix_expires_in_minutes": 60,
    "description": "PIX de Teste - Cliente Customizado",
    "customer": {
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900",
      "phone": "11999999999"
    }
  }'
```

### Exemplo 4: Usando Basic Auth

```bash
curl --request POST \
  --url https://api.seudominio.com/v2/transactions/test-pix \
  --user "PB-playpayments-SUA_PUBLIC_KEY:SK-playpayments-SUA_PRIVATE_KEY" \
  --header 'Content-Type: application/json' \
  --data '{
    "amount": 15.00
  }'
```

---

## Resposta de Sucesso (201)

```json
{
  "success": true,
  "message": "PIX de teste criado com sucesso",
  "gateway": {
    "id": 1,
    "name": "SharkGateway",
    "slug": "sharkgateway",
    "type": "sharkgateway"
  },
  "data": {
    "id": 29112548,
    "tenantId": "5ebcc006-82a2-4b67-8487-cebceddf4ee4",
    "companyId": 17363,
    "amount": 1000,
    "currency": "BRL",
    "paymentMethod": "pix",
    "status": "waiting_payment",
    "installments": 1,
    "paidAt": null,
    "paidAmount": 0,
    "refundedAt": null,
    "refundedAmount": 0,
    "redirectUrl": null,
    "returnUrl": null,
    "postbackUrl": null,
    "metadata": null,
    "ip": "192.168.1.1",
    "externalRef": "TEST_PIX_1234567890_abc123",
    "secureId": "TXN_1234567890",
    "secureUrl": "TXN_1234567890",
    "createdAt": "2024-01-20T15:30:00.000000Z",
    "updatedAt": "2024-01-20T15:30:00.000000Z",
    "payer": null,
    "traceable": false,
    "authorizationCode": null,
    "basePrice": null,
    "interestRate": null,
    "items": [
      {
        "title": "PIX de Teste - API v2",
        "quantity": 1,
        "tangible": false,
        "unitPrice": 1000,
        "externalRef": ""
      }
    ],
    "customer": {
      "id": null,
      "name": "João Silva",
      "email": "joao@example.com",
      "phone": "11999999999",
      "birthdate": null,
      "createdAt": "2024-01-20T15:30:00.000000Z",
      "externalRef": null,
      "document": {
        "type": "cpf",
        "number": "12345678900"
      },
      "address": null
    },
    "fee": {
      "netAmount": 950,
      "estimatedFee": 50,
      "fixedAmount": 0,
      "spreadPercent": 0,
      "currency": "BRL"
    },
    "splits": [
      {
        "amount": 1000,
        "netAmount": 950,
        "recipientId": 17363,
        "chargeProcessingFee": false
      }
    ],
    "refunds": [],
    "pix": {
      "qrcode": "00020101021226940014br.gov.bcb.pix2572qrcode.hyperwalletip.com.br/dynamic/8016df01-c3a6-4044-9120-7b617b129f065204000053039865802BR5908BOA LTDA6009Sao Paulo62070503***6304F2EA",
      "end2EndId": null,
      "receiptUrl": null,
      "expirationDate": "2024-01-20"
    },
    "boleto": null,
    "card": null,
    "refusedReason": null,
    "shipping": null,
    "delivery": null,
    "threeDS": {
      "redirectUrl": null,
      "returnUrl": null
    }
  }
}
```

---

## Respostas de Erro

### 401 - Não Autorizado

```json
{
  "message": "Não autorizado."
}
```

### 400 - Gateway Não Configurado

```json
{
  "success": false,
  "error": "Nenhum gateway de pagamento configurado para este usuário",
  "message": "Configure um gateway de pagamento no painel administrativo antes de criar transações."
}
```

### 400 - Erro ao Criar Transação

```json
{
  "success": false,
  "error": "Mensagem de erro do gateway"
}
```

---

## Como Funciona

1. **Autenticação**: A API identifica o usuário através das chaves Public e Private
2. **Gateway Automático**: Busca automaticamente o gateway configurado do usuário no banco de dados
3. **Criação**: Cria uma transação PIX de teste usando o gateway encontrado
4. **Resposta**: Retorna os dados completos da transação no formato v2

---

## Notas Importantes

1. **Gateway Obrigatório**: O usuário deve ter um gateway configurado no banco de dados
2. **Valores em Reais**: O campo `amount` é em reais (ex: 10.00 = R$ 10,00)
3. **Expiração Padrão**: O PIX expira em 15 minutos por padrão
4. **Dados do Cliente**: Se não informados, usa os dados do usuário autenticado
5. **External ID**: Gerado automaticamente com prefixo `TEST_PIX_`

---

## Endpoints Disponíveis

- `POST /v2/transactions/test-pix` (sem prefixo /api)
- `POST /api/v2/transactions/test-pix` (com prefixo /api)
- `POST https://api.seudominio.com/v2/transactions/test-pix` (subdomínio API)

---

## Exemplo Completo em PHP

```php
<?php

$publicKey = 'PB-playpayments-SUA_PUBLIC_KEY';
$privateKey = 'SK-playpayments-SUA_PRIVATE_KEY';
$url = 'https://api.seudominio.com/v2/transactions/test-pix';

$data = [
    'amount' => 20.00,
    'pix_expires_in_minutes' => 30,
    'description' => 'Teste de PIX via API'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Public-Key: ' . $publicKey,
    'X-Private-Key: ' . $privateKey
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($httpCode === 201 && $result['success']) {
    echo "PIX criado com sucesso!\n";
    echo "QR Code: " . $result['data']['pix']['qrcode'] . "\n";
    echo "Status: " . $result['data']['status'] . "\n";
} else {
    echo "Erro: " . ($result['error'] ?? $result['message'] ?? 'Erro desconhecido') . "\n";
}
```

---

## Exemplo em JavaScript (Fetch)

```javascript
const publicKey = 'PB-playpayments-SUA_PUBLIC_KEY';
const privateKey = 'SK-playpayments-SUA_PRIVATE_KEY';
const url = 'https://api.seudominio.com/v2/transactions/test-pix';

const data = {
    amount: 20.00,
    pix_expires_in_minutes: 30,
    description: 'Teste de PIX via API'
};

fetch(url, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-Public-Key': publicKey,
        'X-Private-Key': privateKey
    },
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(result => {
    if (result.success) {
        console.log('PIX criado com sucesso!');
        console.log('QR Code:', result.data.pix.qrcode);
        console.log('Status:', result.data.status);
    } else {
        console.error('Erro:', result.error || result.message);
    }
})
.catch(error => {
    console.error('Erro na requisição:', error);
});
```

---

## Suporte

Para dúvidas ou suporte, entre em contato através do painel administrativo.


