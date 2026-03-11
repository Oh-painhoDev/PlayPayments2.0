# 📚 Documentação Completa - playpayments Gateway

**Última Atualização:** 2025-01-20

---

## 📋 Índice

1. [API PIX - Guia Rápido](#1-api-pix---guia-rápido)
2. [API v1/transactions - Documentação Completa](#2-api-v1transactions---documentação-completa)
3. [Endpoints da API](#3-endpoints-da-api)
4. [Configuração de Subdomínios](#4-configuração-de-subdomínios)
5. [Integração SharkBanking](#5-integração-sharkbanking)
6. [Integração UTMify](#6-integração-utmify)
7. [Exemplos de Uso](#7-exemplos-de-uso)
8. [Troubleshooting](#8-troubleshooting)

---

## 1. API PIX - Guia Rápido

### 🎯 O Que Você Precisa

1. **Public Key** (PB-playpayments-...) - Chave pública
2. **Private Key** (SK-playpayments-...) - Chave secreta (Secret Key)
3. **URL da API** - Exemplo: `https://seu-dominio.com/api/v1/transactions`

---

### 🔥 Endpoints Principais

#### 1️⃣ Criar PIX (Gerar QR Code)

**POST** `/api/v1/transactions`

**Headers Obrigatórios:**
```
X-Public-Key: PB-playpayments-sua-chave-publica
X-Private-Key: SK-playpayments-sua-chave-secreta
Content-Type: application/json
```

**Body JSON:**
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

**Resposta de Sucesso:**
```json
{
  "success": true,
  "data": {
    "id": "TXN_ABC123",
    "transaction_id": "TXN_ABC123",
    "status": "pending",
    "amount": 50.00,
    "pix": {
      "qr_code": "00020126580014br.gov.bcb.pix...",
      "payload": "00020126580014br.gov.bcb.pix...",
      "qrcode": "00020126580014br.gov.bcb.pix..."
    },
    "expires_at": "2024-01-15T14:30:00.000000Z"
  }
}
```

---

#### 2️⃣ Consultar Status do PIX

**GET** `/api/v1/transactions/{transaction_id}`

**Headers:**
```
Authorization: Bearer SK-playpayments-sua-chave-secreta
```

**OU**

```
Authorization: Bearer PB-playpayments-sua-chave-publica
```

**Resposta:**
```json
{
  "success": true,
  "data": {
    "id": "TXN_ABC123",
    "status": "paid",
    "amount": 50.00,
    "paid_at": "2024-01-15T14:15:00.000000Z"
  }
}
```

**Status Possíveis:**
- `pending` - Aguardando pagamento
- `paid` - Pago ✅
- `expired` - Expirado
- `cancelled` - Cancelado

---

### 📋 Campos Importantes

#### Para Criar PIX:

| Campo | Obrigatório | Tipo | Descrição |
|-------|-------------|------|-----------|
| `amount` | ✅ Sim | number | Valor (ex: 50.00) |
| `payment_method` | ✅ Sim | string | Sempre `"pix"` |
| `customer.name` | ✅ Sim | string | Nome do cliente |
| `customer.email` | ✅ Sim | string | Email válido |
| `customer.document` | ✅ Sim | string | CPF/CNPJ (só números) |
| `customer.phone` | ❌ Não | string | Telefone (só números) |
| `description` | ❌ Não | string | Descrição do pagamento |
| `external_id` | ❌ Não | string | Seu ID único |
| `pix_expires_in_minutes` | ❌ Não | integer | Minutos até expirar (padrão: 15) |

---

### 🚀 Exemplo Rápido cURL

#### Criar PIX:
```bash
curl -X POST "https://seu-dominio.com/api/v1/transactions" \
  -H "X-Public-Key: PB-playpayments-sua-chave" \
  -H "X-Private-Key: SK-playpayments-sua-chave" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 50.00,
    "payment_method": "pix",
    "customer": {
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900"
    }
  }'
```

#### Consultar Status:
```bash
curl -X GET "https://seu-dominio.com/api/v1/transactions/TXN_ABC123" \
  -H "Authorization: Bearer SK-playpayments-sua-chave"
```

---

## 2. API v1/transactions - Documentação Completa

### 🔐 Autenticação

#### Para Criar Transação (POST) - Requer AMBOS os Tokens

Para criar uma transação PIX, você **DEVE** fornecer **AMBOS** os tokens:
- **Public Key** (PB-playpayments-...)
- **Private Key** (SK-playpayments-...) também conhecida como Secret Key

#### Para Consultar Transação (GET) - Aceita Um Token

Para consultar transações, você pode usar apenas um dos tokens:
- **Public Key** (PB-playpayments-...) - somente leitura
- **Private Key** (SK-playpayments-...) - leitura e escrita

---

### 📍 Endpoints

#### 1. Criar Venda (Gerar PIX)

**POST** `/api/v1/transactions`

**Headers Obrigatórios:**

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

**Body (JSON):**

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

**Parâmetros:**

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `amount` | number | Sim | Valor da transação (mínimo 0.01) |
| `payment_method` | string | Sim | Método: `pix`, `credit_card`, `bank_slip` |
| `customer.name` | string | Sim | Nome do cliente |
| `customer.email` | string | Sim | Email do cliente |
| `customer.document` | string | Sim | CPF/CNPJ (apenas números) |
| `customer.phone` | string | Não | Telefone (apenas números) |
| `description` | string | Não | Descrição da transação |
| `external_id` | string | Não | ID externo único da transação |
| `pix_expires_in_minutes` | integer | Não | Tempo de expiração em minutos (padrão: 15) |
| `expires_in` | integer | Não | Tempo de expiração em segundos (alternativa) |

**Resposta de Sucesso (201):**

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

---

#### 2. Buscar Venda (Consultar Status)

**GET** `/api/v1/transactions/{id}`

**Headers:**

```
Authorization: Bearer SK-playpayments-seu-private-key-aqui
```

**OU**

```
Authorization: Bearer PB-playpayments-seu-public-key-aqui
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

---

### 📊 Status da Transação

Os possíveis valores de `status` são:

- `pending` - Aguardando pagamento
- `processing` - Processando
- `paid` - Pago
- `expired` - Expirado
- `cancelled` - Cancelado
- `failed` - Falhou
- `refunded` - Estornado

---

## 3. Endpoints da API

### Autenticação JWT

- `POST /auth/login` - Login
- `POST /auth/refresh` - Refresh token
- `POST /auth/logout` - Logout (requer autenticação)
- `GET /auth/me` - Dados do usuário (requer autenticação)

### PIX (v1 - Compatível com PodPay)

- `GET /v1/transactions` - Listar transações (requer API Key)
- `GET /v1/transactions/{id}` - Buscar transação (requer API Key)
- `POST /v1/transactions` - Criar transação PIX (requer Public Key + Private Key)

### Payments

- `POST /payments` - Criar pagamento (requer API Key)
- `GET /payments` - Listar pagamentos (requer API Key)
- `GET /payments/{transactionId}` - Buscar pagamento (requer API Key)
- `GET /payments/status/{transactionId}` - Status do pagamento (requer API Key)

### Withdrawals (PIX OUT)

- `POST /withdrawals` - Criar saque (requer API Key)
- `GET /withdrawals` - Listar saques (requer API Key)
- `GET /withdrawals/{withdrawalId}` - Buscar saque (requer API Key)
- `GET /withdrawals/status/{withdrawalId}` - Status do saque (requer API Key)

### PIX

- `POST /pix` - Criar PIX (requer API Key)
- `GET /pix` - Listar PIX (requer API Key)
- `GET /pix/{transactionId}` - Buscar PIX (requer API Key)
- `GET /pix/status/{transactionId}` - Status do PIX (requer API Key)

### Test

- `GET /test` - Teste da API (requer API Key)
- `POST /test/pix` - Teste de PIX (requer API Key)
- `POST /test-pix-simple` - Teste simples de PIX (sem autenticação - apenas desenvolvimento)

### External PIX API

- `POST /external-pix/create` - Criar PIX externo (requer API Key)
- `GET /external-pix/status/{transactionId}` - Status do PIX externo (requer API Key)

### Utmify API

- `POST /utmify/generate-pix` - Gerar PIX e enviar para UTMify (requer API Key)

---

## 4. Configuração de Subdomínios

### ⚠️ IMPORTANTE
Os arquivos de rotas já estão configurados! Você só precisa criar os registros DNS.

---

### 🔍 Passo 1: Descobrir o IP do Seu Servidor

```bash
# No servidor Linux/Mac
curl ifconfig.me

# Ou acesse:
https://www.whatismyip.com/
```

---

### 🌐 Passo 2: Configurar DNS no Provedor

#### Opção A: Registro.br

1. Acesse: https://registro.br/
2. Faça login
3. Vá em **Meus Domínios** → **playpayments.com** → **DNS**
4. Adicione os registros:

```
Host: api
Tipo: A
Valor: [SEU_IP_AQUI]
TTL: 3600

Host: app
Tipo: A
Valor: [SEU_IP_AQUI]
TTL: 3600
```

#### Opção B: Cloudflare

1. Acesse: https://dash.cloudflare.com/
2. Selecione o domínio **playpayments.com**
3. Vá em **DNS** → **Records**
4. Adicione:

**Registro 1:**
```
Type: A
Name: api
IPv4 address: [SEU_IP_AQUI]
Proxy status: DNS only
TTL: Auto
```

**Registro 2:**
```
Type: A
Name: app
IPv4 address: [SEU_IP_AQUI]
Proxy status: DNS only
TTL: Auto
```

---

### 🧪 Passo 3: Testar Localmente (Desenvolvimento)

#### Windows:
1. Abra o Bloco de Notas **como Administrador**
2. Abra o arquivo: `C:\Windows\System32\drivers\etc\hosts`
3. Adicione no final:
```
127.0.0.1    api.playpayments.com
127.0.0.1    app.playpayments.com
```

#### Linux/Mac:
```bash
sudo nano /etc/hosts
```

Adicione:
```
127.0.0.1    api.playpayments.com
127.0.0.1    app.playpayments.com
```

### Depois, inicie o Laravel:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Acesse:
- http://api.playpayments.com:8000/health
- http://app.playpayments.com:8000/

---

### ✅ Passo 4: Verificar se Funcionou

```bash
# Windows CMD
nslookup api.playpayments.com
nslookup app.playpayments.com

# Ou PowerShell
Resolve-DnsName api.playpayments.com
Resolve-DnsName app.playpayments.com
```

**Verificação Online:**
- https://dnschecker.org/
  - Digite: `api.playpayments.com` e `app.playpayments.com`
  - Verifique se aparece seu IP em vários servidores DNS

---

### 🔒 Passo 5: Configurar SSL (HTTPS)

```bash
# Instalar certbot
sudo apt-get install certbot python3-certbot-nginx

# Gerar certificado para ambos subdomínios
sudo certbot --nginx -d playpayments.com -d api.playpayments.com -d app.playpayments.com
```

---

### 🎯 Resultado Final

Após configurar, você terá:
- `api.playpayments.com` → Todas as rotas de API
- `app.playpayments.com` → Todas as rotas web (dashboard, login, etc.)
- `playpayments.com` → Continua funcionando normalmente

---

## 5. Integração SharkBanking

### 🐊 Teste Completo - SharkBanking Integration

#### Método 1: Usando o Comando Artisan

```bash
php artisan test:sharkbanking \
    --user_id=1 \
    --amount=150.00 \
    --payment_method=pix \
    --sale_name="Produto Teste" \
    --description="Descrição do produto" \
    --pix_expires_in_days=7 \
    --customer_name="João Silva" \
    --customer_email="joao@example.com" \
    --customer_document="12345678900" \
    --customer_phone="11999999999"
```

---

### 🔧 Parâmetros Disponíveis

#### Configurações Básicas

| Parâmetro | Tipo | Obrigatório | Padrão | Descrição |
|-----------|------|-------------|--------|-----------|
| `--user_id` | integer | ✅ Sim | - | ID do usuário |
| `--gateway_id` | integer | ❌ Não | Gateway do usuário | ID do gateway (opcional) |
| `--amount` | float | ❌ Não | 100.00 | Valor da transação |
| `--payment_method` | string | ❌ Não | pix | Método: `pix`, `credit_card`, `bank_slip` |

#### Informações do Produto/Venda

| Parâmetro | Tipo | Obrigatório | Padrão | Descrição |
|-----------|------|-------------|--------|-----------|
| `--sale_name` | string | ❌ Não | "Produto Teste" | **Nome da venda/produto** |
| `--description` | string | ❌ Não | "Descrição do produto..." | **Descrição detalhada** |

#### Configurações PIX

| Parâmetro | Tipo | Obrigatório | Padrão | Descrição |
|-----------|------|-------------|--------|-----------|
| `--pix_expires_in_minutes` | integer | ❌ Não | 15 | Tempo em minutos (< 24h) |
| `--pix_expires_in_days` | integer | ❌ Não | null | Tempo em dias (1-90, >= 24h) |

**⚠️ IMPORTANTE:** 
- Use `pix_expires_in_minutes` para valores **< 24 horas** (15 min a 1439 min)
- Use `pix_expires_in_days` para valores **>= 1 dia** (1 a 90 dias)
- Se `pix_expires_in_days` for preenchido, será usado **ao invés de** minutes
- A API SharkBanking usa:
  - `expiresIn` (em **segundos**) para valores < 1 dia
  - `expiresInDays` (dias **inteiros**) para valores >= 1 dia

---

### 📝 Exemplos Práticos

#### Exemplo 1: PIX com 15 minutos
```bash
php artisan test:sharkbanking \
    --user_id=1 \
    --amount=50.00 \
    --payment_method=pix \
    --sale_name="Curso Online" \
    --description="Acesso ao curso completo por 30 dias" \
    --pix_expires_in_minutes=15 \
    --customer_name="Maria Santos" \
    --customer_email="maria@example.com" \
    --customer_document="98765432100" \
    --customer_phone="11988888888"
```

#### Exemplo 2: PIX com 7 dias
```bash
php artisan test:sharkbanking \
    --user_id=1 \
    --amount=250.00 \
    --payment_method=pix \
    --sale_name="Assinatura Mensal" \
    --description="Plano mensal de serviços premium" \
    --pix_expires_in_days=7 \
    --customer_name="Carlos Oliveira" \
    --customer_email="carlos@example.com" \
    --customer_document="11122233344" \
    --customer_phone="11977777777"
```

---

### ⚙️ Valores de Expiração PIX

| Tempo | Minutos | Dias | API Usa |
|-------|---------|------|---------|
| 15 minutos | 15 | - | `expiresIn: 900` (segundos) |
| 30 minutos | 30 | - | `expiresIn: 1800` (segundos) |
| 1 hora | 60 | - | `expiresIn: 3600` (segundos) |
| 12 horas | 720 | - | `expiresIn: 43200` (segundos) |
| 1 dia | 1440 | 1 | `expiresInDays: 1` |
| 7 dias | - | 7 | `expiresInDays: 7` |
| 30 dias | - | 30 | `expiresInDays: 30` |
| 90 dias | - | 90 | `expiresInDays: 90` (máximo) |

---

## 6. Integração UTMify

### 🔧 Correção UTMify - Guia Completo

#### ✅ O QUE FOI CORRIGIDO

1. **Payload Corrigido**
   - ✅ `customer.phone` e `customer.document` agora são **sempre enviados** (podem ser `null`)
   - ✅ `customer.ip` é **omitido** se for `null` (API não aceita)
   - ✅ `approvedDate` e `refundedAt` são **sempre enviados** (podem ser `null`)
   - ✅ `planId` e `planName` são **sempre enviados** (podem ser `null`)
   - ✅ `trackingParameters` é um **objeto completo** (não array vazio)

2. **Limpeza Automática de Token**
   - ✅ Token é limpo automaticamente (remove espaços/quebras de linha)
   - ✅ Token é atualizado no banco se foi limpo

3. **Logs Melhorados**
   - ✅ Logs detalhados para diagnóstico
   - ✅ Logs específicos para erro de token inválido

4. **Nome do Produto**
   - ✅ Prioriza o nome do produto (`sale_name` ou `products[0].title/name`)
   - ✅ Não envia mais a descrição da transação como nome do produto

---

### 🔧 SOLUÇÃO - Atualizar Token UTMify

#### Passo 1: Obter Token Válido da UTMify

1. Acesse: https://utmify.com.br
2. Faça login na sua conta
3. Vá em: **Integrações** > **Webhooks** > **Credenciais de API**
4. Verifique se há uma credencial ativa
5. Se não houver ou estiver inativa:
   - Clique em **Adicionar Credencial**
   - Crie uma nova credencial
   - **Copie o token EXATO** (sem espaços)

#### Passo 2: Atualizar Token no Banco

**Opção A: Usar Script Automático (Recomendado)**

```bash
php public/update-utmify-token.php "SEU_TOKEN_AQUI"
```

**Opção B: Atualizar Manualmente no Banco**

```sql
UPDATE utmify_integrations 
SET api_token = 'SEU_TOKEN_AQUI' 
WHERE id = 2;
```

**IMPORTANTE:** 
- Copie o token EXATAMENTE (sem espaços no início/fim)
- Não adicione quebras de linha
- O token deve ter pelo menos 20 caracteres

---

### 🧪 TESTES

#### Testar Token Manualmente
```bash
php public/test-utmify-with-token.php
```

#### Testar Payload Completo
```bash
php public/test-utmify-full-debug.php
```

#### Testar PIX e UTMify
```bash
curl -X POST http://localhost:8000/api/test-pix-simple \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 2,
    "amount": 10.00
  }'
```

---

### 📋 CHECKLIST

- [ ] Token válido obtido da UTMify
- [ ] Token atualizado no banco de dados
- [ ] Token testado e validado
- [ ] PIX de teste criado
- [ ] Logs verificados (deve mostrar "SUCESSO")
- [ ] Transação aparece na UTMify
- [ ] Nome do produto está sendo enviado corretamente

---

## 7. Exemplos de Uso

### JavaScript (Fetch API)

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

---

### PHP

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

## 8. Troubleshooting

### ⚠️ Erros Comuns

#### Erro 401 - Não Autorizado
```
Verifique se as chaves estão corretas
Para criar PIX precisa de AMBAS as chaves (Public + Private)
```

#### Erro 422 - Dados Inválidos
```
Verifique se todos os campos obrigatórios foram preenchidos
Email deve ser válido
Documento deve ter pelo menos 11 dígitos
```

#### Erro 400 - Gateway Não Configurado
```
O usuário não tem gateway de pagamento configurado
Entre em contato com o suporte
```

---

### 🔍 Diagnóstico

#### DNS não resolve
- **Solução:** Aguarde mais tempo (propagação DNS)
- **Solução:** Verifique se criou os registros A corretamente
- **Solução:** Use `nslookup` para verificar

#### Erro 404 ou "Site não encontrado"
- **Solução:** Verifique se o servidor web (Apache/Nginx) está configurado para aceitar os subdomínios
- **Solução:** Verifique se os arquivos de rotas foram carregados corretamente

#### PIX não está sendo enviado para UTMify?
1. **Verifique se o usuário tem integração UTMify:**
   ```sql
   SELECT * FROM utmify_integrations WHERE user_id = 2 AND is_active = 1;
   ```

2. **Verifique se `trigger_on_creation` está habilitado:**
   ```sql
   SELECT id, name, is_active, trigger_on_creation, trigger_on_payment 
   FROM utmify_integrations 
   WHERE user_id = 2;
   ```

3. **Verifique os logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep "UTMify:"
   ```

---

### 💡 Dicas

1. **Sempre salve o `transaction_id`** retornado na criação
2. **Use o `qr_code`** para gerar a imagem do QR Code
3. **Consulte o status** a cada 5-10 segundos enquanto aguarda pagamento
4. **O PIX expira** em 15 minutos por padrão (ou o tempo que você definir)
5. **Use `external_id`** para relacionar com seu sistema interno

---

## 📞 Suporte

Se tiver problemas, verifique:
- ✅ Chaves estão corretas?
- ✅ URL da API está correta?
- ✅ Todos os campos obrigatórios foram enviados?
- ✅ O formato JSON está correto?

---

**🎉 Documentação Completa - playpayments Gateway**

**Versão:** 1.0  
**Última Atualização:** 2025-01-20



