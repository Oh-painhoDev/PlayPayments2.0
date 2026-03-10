# API v2 - Criar Venda

## Endpoint

**POST** `https://api.seudominio.com/v2/transactions`

Para processar uma transação, utilize a rota `/v2/transactions`. Essa rota é compatível com pagamentos via cartão de crédito, boleto bancário e PIX.

Para criar uma venda internacional é necessário fornecer os campos `amount` e `currency` seguindo o padrão informado na página Moedas suportadas.

---

## Autenticação

Esta rota requer autenticação usando **Basic Auth** ou **Bearer Token** com suas credenciais de API:

- **Public Key** (username)
- **Private Key** (password)

### Exemplo de Autenticação

```bash
# Usando Basic Auth
curl -X POST https://api.seudominio.com/v2/transactions \
  -u "SUA_PUBLIC_KEY:SUA_PRIVATE_KEY" \
  -H "Content-Type: application/json" \
  -d '{ ... }'
```

```bash
# Usando Bearer Token
curl -X POST https://api.seudominio.com/v2/transactions \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{ ... }'
```

---

## Body Parameters

### Campos Obrigatórios

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `amount` | `integer` | Valor total da transação em centavos. (Ex: 100 = R$ 1,00) |
| `paymentMethod` | `string` | Meio de pagamento. Valores possíveis: `credit_card`, `boleto`, `pix` |
| `customer` | `object` | Informações sobre o cliente (obrigatório) |
| `customer.name` | `string` | Nome do cliente |
| `customer.email` | `string` | E-mail do cliente |
| `customer.document` | `string` | CPF/CNPJ do cliente (apenas números) |
| `items` | `array` | Lista dos produtos vendidos. Ao menos um item é obrigatório |

### Campos Opcionais

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `currency` | `string` | Código da moeda utilizada na transação, conforme o padrão ISO 4217 (ex: BRL, USD, EUR). Padrão: `BRL` |
| `installments` | `integer` | Quantidade de parcelas. Obrigatório caso `paymentMethod` seja `credit_card` |
| `card` | `object` | Informações do cartão do cliente. Obrigatório caso `paymentMethod` seja `credit_card` |
| `pix` | `object` | Informações sobre a expiração do PIX |
| `pix.expiresInDays` | `integer` | Tempo de expiração do PIX em dias. Padrão: 15 minutos |
| `pix_expires_in_minutes` | `integer` | Tempo de expiração do PIX em minutos (alternativa ao objeto pix) |
| `boleto` | `object` | Informações sobre a expiração do boleto |
| `shipping` | `object` | Endereço de entrega. Obrigatório caso algum produto seja físico (tangible: true) |
| `shipping.fee` | `number` | Taxa de entrega em reais (será convertida para centavos) |
| `shipping.address` | `object` | Endereço completo de entrega |
| `shipping.address.street` | `string` | Nome da rua |
| `shipping.address.streetNumber` | `string` | Número do endereço |
| `shipping.address.neighborhood` | `string` | Nome do bairro |
| `shipping.address.city` | `string` | Nome da cidade |
| `shipping.address.state` | `string` | UF (2 dígitos em letra maiúscula, exemplo: SP) |
| `shipping.address.zipCode` | `string` | CEP (apenas números) |
| `shipping.address.country` | `string` | País (2 dígitos, exemplo: BR) |
| `shipping.address.complement` | `string` | Complemento do endereço (opcional) |
| `customer.phone` | `string` | Telefone do cliente no formato 11999999999 |
| `postbackUrl` | `string` | URL em sua API para receber atualizações desta única transação |
| `returnUrl` | `string` | URL onde o cliente será redirecionado após a finalização do processo de 3DS |
| `metadata` | `string` | Informações relevantes sobre esta transação no checkout |
| `externalRef` | `string` | Referência desta transação em sua API |
| `ip` | `string` | IP do cliente |
| `description` | `string` | Descrição da transação |

---

## Estrutura de Objetos

### customer (obrigatório)

```json
{
  "name": "João Silva",
  "email": "joao@example.com",
  "document": "12345678900",
  "phone": "11999999999"
}
```

### items (obrigatório - array)

Cada item pode conter os seguintes campos:

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `title` | `string` | Sim | Título do produto (usado como fallback) |
| `name` | `string` | Não | Nome do produto (enviado para adquirente) |
| `description` | `string` | Não | Descrição detalhada do produto (enviado para adquirente) |
| `quantity` | `integer` | Não | Quantidade do produto (padrão: 1) |
| `unitPrice` | `integer` | Não | Preço unitário em centavos |
| `price` | `integer` | Não | Preço do produto em centavos (alternativa ao unitPrice) |
| `tangible` | `boolean` | Não | Indica se o produto é físico (padrão: false) |

```json
[
  {
    "title": "Produto 1",
    "name": "Nome do Produto para Adquirente",
    "description": "Descrição detalhada do produto que será enviada para a adquirente",
    "quantity": 1,
    "unitPrice": 10000,
    "price": 10000,
    "tangible": false
  }
]
```

### card (obrigatório para credit_card)

```json
{
  "number": "4111111111111111",
  "holderName": "JOAO SILVA",
  "expirationMonth": "12",
  "expirationYear": "2025",
  "cvv": "123"
}
```

### pix (opcional)

```json
{
  "expiresInDays": 1
}
```

Ou use o campo direto:

```json
{
  "pix_expires_in_minutes": 1440
}
```

### boleto (opcional)

```json
{
  "expiresInDays": 3
}
```

### shipping (obrigatório se algum produto for físico)

```json
{
  "fee": 1000,
  "address": {
    "street": "Rua Exemplo",
    "streetNumber": "123",
    "neighborhood": "Centro",
    "city": "São Paulo",
    "state": "SP",
    "zipCode": "01234567",
    "country": "BR",
    "complement": "Apto 45"
  }
}
```

---

## Exemplos de Requisição

### Exemplo 1: Pagamento PIX Simples

```bash
curl --request POST \
  --url https://api.seudominio.com/v2/transactions \
  --header 'accept: application/json' \
  --header 'authorization: Basic SUA_PUBLIC_KEY:SUA_PRIVATE_KEY' \
  --header 'content-type: application/json' \
  --data '{
    "amount": 10000,
    "currency": "BRL",
    "paymentMethod": "pix",
    "customer": {
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900",
      "phone": "11999999999"
    },
    "items": [
      {
        "title": "Produto Teste",
        "name": "Nome do Produto para Adquirente",
        "description": "Descrição detalhada do produto",
        "quantity": 1,
        "unitPrice": 10000,
        "tangible": false
      }
    ],
    "description": "Venda via API v2",
    "externalRef": "PEDIDO-12345"
  }'
```

### Exemplo 2: Pagamento PIX com Expiração Customizada

```bash
curl --request POST \
  --url https://api.seudominio.com/v2/transactions \
  --header 'accept: application/json' \
  --header 'authorization: Basic SUA_PUBLIC_KEY:SUA_PRIVATE_KEY' \
  --header 'content-type: application/json' \
  --data '{
    "amount": 50000,
    "currency": "BRL",
    "paymentMethod": "pix",
    "pix": {
      "expiresInDays": 1
    },
    "customer": {
      "name": "Maria Santos",
      "email": "maria@example.com",
      "document": "98765432100"
    },
    "items": [
      {
        "title": "Curso Online",
        "name": "Curso Completo de Programação",
        "description": "Curso online completo com 50 horas de conteúdo",
        "quantity": 1,
        "unitPrice": 50000,
        "tangible": false
      }
    ],
    "postbackUrl": "https://seusite.com/webhook/transactions"
  }'
```

### Exemplo 3: Pagamento com Cartão de Crédito

```bash
curl --request POST \
  --url https://api.seudominio.com/v2/transactions \
  --header 'accept: application/json' \
  --header 'authorization: Basic SUA_PUBLIC_KEY:SUA_PRIVATE_KEY' \
  --header 'content-type: application/json' \
  --data '{
    "amount": 100000,
    "currency": "BRL",
    "paymentMethod": "credit_card",
    "installments": 3,
    "card": {
      "number": "4111111111111111",
      "holderName": "JOAO SILVA",
      "expirationMonth": "12",
      "expirationYear": "2025",
      "cvv": "123"
    },
    "customer": {
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900",
      "phone": "11999999999"
    },
    "items": [
      {
        "title": "Notebook",
        "name": "Notebook Dell Inspiron 15",
        "description": "Notebook Dell Inspiron 15 com processador Intel i7, 16GB RAM, SSD 512GB",
        "quantity": 1,
        "unitPrice": 100000,
        "tangible": true
      }
    ],
    "shipping": {
      "fee": 20.00,
      "address": {
        "street": "Rua Exemplo",
        "streetNumber": "123",
        "neighborhood": "Centro",
        "city": "São Paulo",
        "state": "SP",
        "zipCode": "01234567",
        "country": "BR"
      }
    },
    "returnUrl": "https://seusite.com/payment/return"
  }'
```

### Exemplo 4: Pagamento com Boleto

```bash
curl --request POST \
  --url https://api.seudominio.com/v2/transactions \
  --header 'accept: application/json' \
  --header 'authorization: Basic SUA_PUBLIC_KEY:SUA_PRIVATE_KEY' \
  --header 'content-type: application/json' \
  --data '{
    "amount": 50000,
    "currency": "BRL",
    "paymentMethod": "boleto",
    "boleto": {
      "expiresInDays": 3
    },
    "customer": {
      "name": "Pedro Costa",
      "email": "pedro@example.com",
      "document": "11122233344"
    },
    "items": [
      {
        "title": "Serviço Premium",
        "name": "Plano Premium Mensal",
        "description": "Acesso completo a todos os recursos premium por 30 dias",
        "quantity": 1,
        "unitPrice": 50000,
        "tangible": false
      }
    ],
    "externalRef": "SERVICO-001"
  }'
```

---

## Respostas

### 201 - Sucesso

```json
{
  "success": true,
  "data": {
    "id": "TXN_1234567890",
    "transaction_id": "TXN_1234567890",
    "external_id": "PEDIDO-12345",
    "amount": 10000,
    "fee_amount": 100,
    "net_amount": 9900,
    "currency": "BRL",
    "payment_method": "pix",
    "status": "pending",
    "is_retained": false,
    "gateway": {
      "id": 1,
      "name": "Gateway Name",
      "slug": "gateway-slug",
      "type": "gateway_type"
    },
    "customer": {
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900",
      "phone": "11999999999"
    },
    "description": "Venda via API v2",
    "expires_at": "2024-01-20T15:30:00.000000Z",
    "paid_at": null,
    "refunded_at": null,
    "created_at": "2024-01-20T15:15:00.000000Z",
    "updated_at": "2024-01-20T15:15:00.000000Z",
    "pix": {
      "qr_code": "00020126580014br.gov.bcb.pix...",
      "payload": "00020126580014br.gov.bcb.pix...",
      "qrcode": "00020126580014br.gov.bcb.pix...",
      "end_to_end_id": null,
      "txid": "PEDIDO-12345",
      "expiration_date": "2024-01-20T15:30:00.000000Z"
    }
  }
}
```

### 400 - Erro de Validação

```json
{
  "success": false,
  "error": "Invalid data",
  "errors": {
    "amount": ["The amount field is required."],
    "payment_method": ["The payment method field is required."]
  }
}
```

### 401 - Não Autorizado

```json
{
  "success": false,
  "error": "Unauthorized"
}
```

### 404 - Transação Não Encontrada

```json
{
  "success": false,
  "error": "Transaction not found"
}
```

### 500 - Erro Interno

```json
{
  "success": false,
  "error": "Error creating transaction: [mensagem de erro]"
}
```

---

## Campos da Resposta

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | `string` | Identificador único da transação |
| `transaction_id` | `string` | ID da transação (mesmo que `id`) |
| `external_id` | `string` | Referência externa da transação |
| `amount` | `float` | Valor total da transação em centavos |
| `fee_amount` | `float` | Taxa cobrada em centavos |
| `net_amount` | `float` | Valor líquido após taxas em centavos |
| `currency` | `string` | Código da moeda (ex: BRL, USD) |
| `payment_method` | `string` | Método de pagamento (`pix`, `credit_card`, `boleto`) |
| `status` | `string` | Status atual (`pending`, `paid`, `refunded`, `refused`, `expired`) |
| `is_retained` | `boolean` | Indica se a transação está retida |
| `gateway` | `object` | Informações do gateway utilizado |
| `customer` | `object` | Dados do cliente |
| `description` | `string` | Descrição da transação |
| `expires_at` | `string` | Data de expiração (ISO 8601) |
| `paid_at` | `string\|null` | Data do pagamento (ISO 8601) |
| `refunded_at` | `string\|null` | Data do reembolso (ISO 8601) |
| `created_at` | `string` | Data de criação (ISO 8601) |
| `updated_at` | `string` | Data de atualização (ISO 8601) |
| `pix` | `object\|null` | Dados do PIX (se aplicável) |
| `pix.qr_code` | `string` | Código QR do PIX |
| `pix.payload` | `string` | Payload do PIX |
| `pix.expiration_date` | `string` | Data de expiração do PIX |

---

## Status da Transação

| Status | Descrição |
|--------|-----------|
| `pending` | Aguardando pagamento |
| `paid` | Pagamento confirmado |
| `refunded` | Reembolsado |
| `refused` | Recusado |
| `expired` | Expirado |

---

## Moedas Suportadas

| Código | Moeda |
|--------|-------|
| `BRL` | Real brasileiro (padrão) |
| `USD` | Dólar americano |
| `EUR` | Euro |

---

## Webhooks

Para receber notificações automáticas sobre mudanças no status da transação, configure o campo `postbackUrl` na requisição ou configure webhooks globais no painel administrativo.

O webhook será enviado com os seguintes dados:

```json
{
  "event": "transaction.updated",
  "transaction": {
    "id": "TXN_1234567890",
    "status": "paid",
    "amount": 10000,
    ...
  }
}
```

---

## Notas Importantes

1. **Valor em Centavos**: Todos os valores monetários devem ser enviados em centavos (ex: R$ 100,00 = 10000)

2. **Documento do Cliente**: Envie apenas números (sem pontos, traços ou barras)

3. **Telefone**: Envie apenas números (ex: 11999999999)

4. **Expiração PIX**: Padrão de 15 minutos se não especificado

5. **Cartão de Crédito**: Requer autenticação 3DS em alguns casos. Configure `returnUrl` para redirecionamento

6. **Produtos Físicos**: Se algum item tiver `tangible: true`, é obrigatório enviar `shipping`

7. **Rate Limiting**: A API possui limite de requisições por minuto. Consulte seu plano para detalhes

---

## Suporte

Para dúvidas ou suporte, entre em contato através do painel administrativo ou envie um e-mail para suporte@seudominio.com

