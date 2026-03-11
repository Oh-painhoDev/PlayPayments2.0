# Exemplos de Uso da API

## Exemplos de Requisições

### 1. Health Check (Sem Autenticação)

```bash
curl http://api.localhost:8000/health
```

**Resposta:**
```json
{
    "status": "ok",
    "service": "API Subdomain",
    "timestamp": "2025-11-12T10:30:00.000000Z"
}
```

### 2. Acessar API (Sem Autenticação)

```bash
curl http://api.localhost:8000/acessar
```

**Resposta:**
```json
{
    "success": false,
    "error": "Não autorizado.",
    "message": "Você precisa estar autenticado para acessar esta API. Use: Authorization: Bearer SEU_TOKEN_AQUI"
}
```

### 3. Listar Transações (Com Public Key)

```bash
curl -X GET http://api.localhost:8000/v1/transactions \
  -H "Authorization: Bearer PB-playpayments-SUA-PUBLIC-KEY-AQUI" \
  -H "Accept: application/json"
```

### 4. Buscar Transação (Com Public Key)

```bash
curl -X GET http://api.localhost:8000/v1/transactions/12345 \
  -H "Authorization: Bearer PB-playpayments-SUA-PUBLIC-KEY-AQUI" \
  -H "Accept: application/json"
```

### 5. Criar Transação PIX (Com Public Key + Private Key)

```bash
curl -X POST http://api.localhost:8000/v1/transactions \
  -H "X-Public-Key: PB-playpayments-SUA-PUBLIC-KEY-AQUI" \
  -H "X-Private-Key: SK-playpayments-SUA-PRIVATE-KEY-AQUI" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "amount": 10000,
    "currency": "BRL",
    "paymentMethod": "pix",
    "customer": {
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900"
    },
    "description": "Pagamento de teste"
  }'
```

### 6. Criar Transação PIX (Com Bearer Token Combinado)

```bash
curl -X POST http://api.localhost:8000/v1/transactions \
  -H "Authorization: Bearer PB-playpayments-SUA-PUBLIC-KEY-AQUI:SK-playpayments-SUA-PRIVATE-KEY-AQUI" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "amount": 10000,
    "currency": "BRL",
    "paymentMethod": "pix",
    "customer": {
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900"
    },
    "description": "Pagamento de teste"
  }'
```

## Exemplos em PHP

### Health Check

```php
$ch = curl_init('http://api.localhost:8000/health');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
print_r($data);
```

### Listar Transações

```php
$ch = curl_init('http://api.localhost:8000/v1/transactions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer PB-playpayments-SUA-PUBLIC-KEY-AQUI',
    'Accept: application/json'
]);
$response = curl_exec($ch);
curl_close($ch);

$transactions = json_decode($response, true);
print_r($transactions);
```

### Criar Transação PIX

```php
$data = [
    'amount' => 10000,
    'currency' => 'BRL',
    'paymentMethod' => 'pix',
    'customer' => [
        'name' => 'João Silva',
        'email' => 'joao@example.com',
        'document' => '12345678900'
    ],
    'description' => 'Pagamento de teste'
];

$ch = curl_init('http://api.localhost:8000/v1/transactions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-Public-Key: PB-playpayments-SUA-PUBLIC-KEY-AQUI',
    'X-Private-Key: SK-playpayments-SUA-PRIVATE-KEY-AQUI',
    'Content-Type: application/json',
    'Accept: application/json'
]);
$response = curl_exec($ch);
curl_close($ch);

$transaction = json_decode($response, true);
print_r($transaction);
```

## Exemplos em JavaScript (Fetch)

### Health Check

```javascript
fetch('http://api.localhost:8000/health')
  .then(response => response.json())
  .then(data => console.log(data))
  .catch(error => console.error('Erro:', error));
```

### Listar Transações

```javascript
fetch('http://api.localhost:8000/v1/transactions', {
  headers: {
    'Authorization': 'Bearer PB-playpayments-SUA-PUBLIC-KEY-AQUI',
    'Accept': 'application/json'
  }
})
  .then(response => response.json())
  .then(data => console.log(data))
  .catch(error => console.error('Erro:', error));
```

### Criar Transação PIX

```javascript
fetch('http://api.localhost:8000/v1/transactions', {
  method: 'POST',
  headers: {
    'X-Public-Key': 'PB-playpayments-SUA-PUBLIC-KEY-AQUI',
    'X-Private-Key': 'SK-playpayments-SUA-PRIVATE-KEY-AQUI',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    amount: 10000,
    currency: 'BRL',
    paymentMethod: 'pix',
    customer: {
      name: 'João Silva',
      email: 'joao@example.com',
      document: '12345678900'
    },
    description: 'Pagamento de teste'
  })
})
  .then(response => response.json())
  .then(data => console.log(data))
  .catch(error => console.error('Erro:', error));
```

## Códigos de Status HTTP

- `200` - Sucesso
- `201` - Criado com sucesso
- `400` - Bad Request (dados inválidos)
- `401` - Não autorizado (token inválido ou ausente)
- `403` - Acesso negado (conta bloqueada ou sem permissão)
- `404` - Não encontrado
- `500` - Erro interno do servidor

## Respostas de Erro

### Token Ausente

```json
{
    "success": false,
    "error": "Não autorizado.",
    "message": "Token de autorização não fornecido. Use: Authorization: Bearer SK-playpayments-... ou PB-playpayments-..."
}
```

### Token Inválido

```json
{
    "success": false,
    "error": "Não autorizado.",
    "message": "Token de autorização inválido"
}
```

### Conta Bloqueada

```json
{
    "success": false,
    "error": "Não autorizado.",
    "message": "Conta bloqueada. Entre em contato com o suporte para mais informações."
}
```

### Rota Não Encontrada

```json
{
    "success": false,
    "error": "Não autorizado.",
    "message": "Rota não encontrada. Verifique a URL e os headers de autenticação."
}
```

