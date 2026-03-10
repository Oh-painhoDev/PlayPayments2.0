# API PIX v1 - Transactions

API REST para gerenciamento de transações (vendas) via PIX, compatível com PodPay API v1.

## Base URL

```
https://seu-dominio.com/api/v1/transactions
```

## Autenticação

### Para Consultar Transações (GET)

Para listar ou buscar transações, você pode usar **um** dos tokens:

- **Public Key** (somente leitura): `Authorization: Bearer PB-playpayments-...`
- **Private Key** (leitura/escrita): `Authorization: Bearer SK-playpayments-...`

### Para Criar PIX (POST) - REQUER AMBOS OS TOKENS

Para criar uma transação PIX, você **DEVE** fornecer **AMBOS** os tokens:

**Opção 1: Headers Separados (Recomendado)**
```
X-Public-Key: PB-playpayments-seu-public-key-aqui
X-Private-Key: SK-playpayments-seu-private-key-aqui
```

**Opção 2: Header Authorization**
```
Authorization: Bearer PB-playpayments-seu-public-key-aqui:SK-playpayments-seu-private-key-aqui
```

⚠️ **IMPORTANTE**: Para criar PIX, você precisa fornecer **AMBOS** os tokens (Public Key E Private Key).

Veja a documentação completa de autenticação em: [AUTHENTICATION.md](./AUTHENTICATION.md)

## Endpoints

### 1. Listar Vendas

Lista todas as transações do usuário autenticado.

**GET** `/api/v1/transactions`

#### Parâmetros de Query (opcionais)

- `per_page` (integer): Número de itens por página (padrão: 15)
- `status` (string): Filtrar por status (`pending`, `paid`, `cancelled`, etc.)
- `payment_method` (string): Filtrar por método de pagamento (`pix`, `credit_card`, `bank_slip`)
- `start_date` (date): Data inicial (formato: YYYY-MM-DD)
- `end_date` (date): Data final (formato: YYYY-MM-DD)
- `search` (string): Buscar por ID da transação, external_id, nome ou email do cliente

#### Exemplo de Requisição

```bash
curl -X GET "https://seu-dominio.com/api/v1/transactions?per_page=20&status=paid" \
  -H "Authorization: Bearer SEU_API_SECRET"
```

#### Exemplo de Resposta

```json
{
  "success": true,
  "data": [
    {
      "id": "28413239",
      "transaction_id": "28413239",
      "external_id": "PXB_6913EB68D2222_1762913128",
      "amount": 10.00,
      "fee_amount": 0.35,
      "net_amount": 9.65,
      "currency": "BRL",
      "payment_method": "pix",
      "status": "pending",
      "is_retained": false,
      "customer": {
        "name": "João Silva",
        "email": "joao@example.com",
        "document": "12345678900",
        "phone": "11999999999"
      },
      "pix": {
        "qr_code": "00020126910014br.gov...",
        "payload": "00020126910014br.gov...",
        "end_to_end_id": null,
        "expiration_date": "2025-11-12T02:20:31.971Z"
      },
      "description": "Pagamento via API",
      "gateway": {
        "id": 1,
        "name": "Sharkgateway"
      },
      "expires_at": "2025-11-12T02:20:31.971Z",
      "paid_at": null,
      "refunded_at": null,
      "created_at": "2025-11-12T02:05:31.971Z",
      "updated_at": "2025-11-12T02:05:31.971Z"
    }
  ],
  "pagination": {
    "total": 100,
    "per_page": 20,
    "current_page": 1,
    "last_page": 5,
    "from": 1,
    "to": 20
  }
}
```

---

### 2. Buscar Venda

Busca uma transação específica por ID.

**GET** `/api/v1/transactions/{id}`

#### Parâmetros

- `id` (string): ID da transação (`transaction_id`) ou `external_id`

#### Exemplo de Requisição

```bash
curl -X GET "https://seu-dominio.com/api/v1/transactions/28413239" \
  -H "Authorization: Bearer SEU_API_SECRET"
```

#### Exemplo de Resposta

```json
{
  "success": true,
  "data": {
    "id": "28413239",
    "transaction_id": "28413239",
    "external_id": "PXB_6913EB68D2222_1762913128",
    "amount": 10.00,
    "fee_amount": 0.35,
    "net_amount": 9.65,
    "currency": "BRL",
    "payment_method": "pix",
    "status": "pending",
    "is_retained": false,
    "customer": {
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900",
      "phone": "11999999999"
    },
    "pix": {
      "qr_code": "00020126910014br.gov...",
      "payload": "00020126910014br.gov...",
      "end_to_end_id": null,
      "expiration_date": "2025-11-12T02:20:31.971Z"
    },
    "description": "Pagamento via API",
    "gateway": {
      "id": 1,
      "name": "Sharkgateway"
    },
    "expires_at": "2025-11-12T02:20:31.971Z",
    "paid_at": null,
    "refunded_at": null,
    "created_at": "2025-11-12T02:05:31.971Z",
    "updated_at": "2025-11-12T02:05:31.971Z"
  }
}
```

---

### 3. Criar Venda

Cria uma nova transação (venda).

**POST** `/api/v1/transactions`

#### Body (JSON)

```json
{
  "amount": 10.00,
  "payment_method": "pix",
  "customer": {
    "name": "João Silva",
    "email": "joao@example.com",
    "document": "12345678900",
    "phone": "11999999999"
  },
  "description": "Pagamento de produto",
  "external_id": "MEU_ID_UNICO_123",
  "pix_expires_in_minutes": 15,
  "products": [
    {
      "title": "Produto 1",
      "quantity": 1,
      "unitPrice": 10.00,
      "tangible": true
    }
  ]
}
```

#### Campos Obrigatórios

- `amount` (float): Valor da transação (mínimo: 0.01)
- `payment_method` (string): Método de pagamento (`pix`, `credit_card`, `bank_slip`)
- `customer.name` (string): Nome do cliente
- `customer.email` (string): Email do cliente
- `customer.document` (string): CPF ou CNPJ do cliente (11 ou 14 dígitos)

#### Campos Opcionais

- `customer.phone` (string): Telefone do cliente
- `description` (string): Descrição da transação
- `external_id` (string): ID externo único (se não fornecido, será gerado automaticamente)
- `pix_expires_in_minutes` (integer): Tempo de expiração do PIX em minutos (padrão: 15 minutos)
- `expires_in` (integer): Tempo de expiração em segundos (alternativa ao `pix_expires_in_minutes`)
- `installments` (integer): Número de parcelas (para cartão de crédito, padrão: 1)
- `products` (array): Array de produtos/itens da venda

#### Estrutura de Products

```json
{
  "products": [
    {
      "title": "Nome do Produto",
      "name": "Nome alternativo",
      "description": "Descrição do produto",
      "quantity": 1,
      "unitPrice": 10.00,
      "price": 10.00,
      "tangible": true
    }
  ]
}
```

#### Exemplo de Requisição

⚠️ **IMPORTANTE**: Para criar PIX, você **DEVE** fornecer **AMBOS** os tokens (Public Key E Private Key).

**Opção 1: Headers Separados (Recomendado)**
```bash
curl -X POST "https://seu-dominio.com/api/v1/transactions" \
  -H "X-Public-Key: PB-playpayments-seu-public-key-aqui" \
  -H "X-Private-Key: SK-playpayments-seu-private-key-aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 10.00,
    "payment_method": "pix",
    "customer": {
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900"
    },
    "description": "Pagamento via API",
    "pix_expires_in_minutes": 15
  }'
```

**Opção 2: Header Authorization**
```bash
curl -X POST "https://seu-dominio.com/api/v1/transactions" \
  -H "Authorization: Bearer PB-playpayments-seu-public-key-aqui:SK-playpayments-seu-private-key-aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 10.00,
    "payment_method": "pix",
    "customer": {
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900"
    },
    "description": "Pagamento via API",
    "pix_expires_in_minutes": 15
  }'
```

#### Exemplo de Resposta (Sucesso - 201)

```json
{
  "success": true,
  "data": {
    "id": "28413239",
    "transaction_id": "28413239",
    "external_id": "PXB_6913EB68D2222_1762913128",
    "amount": 10.00,
    "fee_amount": 0.35,
    "net_amount": 9.65,
    "currency": "BRL",
    "payment_method": "pix",
    "status": "pending",
    "is_retained": false,
    "customer": {
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900",
      "phone": null
    },
    "pix": {
      "qr_code": "00020126910014br.gov...",
      "payload": "00020126910014br.gov...",
      "end_to_end_id": null,
      "expiration_date": "2025-11-12T02:20:31.971Z"
    },
    "description": "Pagamento via API",
    "gateway": {
      "id": 1,
      "name": "Sharkgateway"
    },
    "expires_at": "2025-11-12T02:20:31.971Z",
    "paid_at": null,
    "refunded_at": null,
    "created_at": "2025-11-12T02:05:31.971Z",
    "updated_at": "2025-11-12T02:05:31.971Z"
  }
}
```

#### Exemplo de Resposta (Erro - 422)

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

## Status de Transação

Os possíveis valores de `status` são:

- `pending`: Aguardando pagamento
- `processing`: Processando
- `paid`: Pago
- `cancelled`: Cancelado
- `expired`: Expirado
- `failed`: Falhou
- `refunded`: Estornado
- `partially_refunded`: Estornado parcialmente
- `chargeback`: Chargeback

## Códigos de Status HTTP

- `200`: Sucesso (GET)
- `201`: Criado com sucesso (POST)
- `400`: Erro na requisição
- `401`: Não autenticado
- `404`: Transação não encontrada
- `422`: Dados inválidos
- `500`: Erro interno do servidor

## Observações

1. **Autenticação**: Todas as rotas requerem autenticação via API Secret no header `Authorization: Bearer SEU_API_SECRET`

2. **Expiração PIX**: Por padrão, transações PIX expiram em 15 minutos. Você pode alterar isso usando `pix_expires_in_minutes` (mínimo: 1 minuto, máximo: 129.600 minutos = 90 dias)

3. **External ID**: Se não fornecido, será gerado automaticamente no formato `TXN_{timestamp}_{uniqid}`

4. **Produtos**: O campo `products` é opcional, mas se fornecido, deve seguir a estrutura especificada acima

5. **Gateway**: O gateway usado será o configurado para o usuário autenticado

## Compatibilidade

Esta API é compatível com a estrutura da PodPay API v1, facilitando a migração de integrações existentes.

