# 🧪 API de Teste PIX - UTMify

## Endpoints Disponíveis

### 1. `/api/test-pix-simple` (POST) - Sem Autenticação
Endpoint simples para criar PIX de teste sem precisar de autenticação.

**Requisição:**
```bash
curl -X POST http://localhost:8000/api/test-pix-simple \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 2,
    "amount": 10.00
  }'
```

**Resposta:**
```json
{
  "success": true,
  "message": "PIX de teste criado com sucesso",
  "transaction": {
    "transaction_id": "PXB_...",
    "external_id": "TEST_PIX_...",
    "amount": 10.00,
    "status": "pending",
    "payment_method": "pix",
    "created_at": "2025-11-12T20:00:00.000000Z"
  },
  "pix": {
    "code": "00020126...",
    "emv": "00020126..."
  },
  "utmify": {
    "integrations_found": 1,
    "sent": true,
    "error": null,
    "integrations": [
      {
        "id": 1,
        "name": "Integração UTMify",
        "is_active": true,
        "trigger_on_creation": true,
        "trigger_on_payment": true
      }
    ]
  },
  "logs": {
    "check": "Verifique os logs em storage/logs/laravel.log",
    "search_for": "UTMify: ou TransactionObserver:"
  }
}
```

### 2. `/api/test/pix` (POST) - Com Autenticação
Endpoint protegido que requer autenticação JWT.

**Requisição:**
```bash
curl -X POST http://localhost:8000/api/test/pix \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "X-API-Key: SUA_API_SECRET" \
  -d '{
    "amount": 10.00
  }'
```

### 3. Interface Web
Acesse: `http://localhost:8000/test-pix-api.php`

Interface visual para testar a criação de PIX e verificar integração UTMify.

## Como Funciona

1. **Criação do PIX:**
   - Cria uma transação PIX usando o gateway configurado do usuário
   - Gera código PIX (QR Code)

2. **Envio para UTMify:**
   - O `TransactionObserver` detecta a criação da transação
   - Verifica se o usuário tem integração UTMify ativa
   - Envia para UTMify se `trigger_on_creation` estiver habilitado
   - Apenas para transações PIX com status `pending`, `paid` ou `refunded`

3. **Verificação:**
   - Verifica integrações UTMify do usuário
   - Testa envio manual para UTMify
   - Retorna informações sobre o envio

## Parâmetros

### `/api/test-pix-simple`
- `user_id` (obrigatório): ID do usuário
- `amount` (opcional): Valor do PIX (padrão: 10.00)

### `/api/test/pix`
- `amount` (obrigatório): Valor do PIX
- Requer autenticação JWT

## Verificação de Logs

Após criar um PIX de teste, verifique os logs:

```bash
tail -f storage/logs/laravel.log | grep -E "UTMify:|TransactionObserver:"
```

Procure por:
- `🔵 TransactionObserver:` - Observer executado
- `🟢 UTMify:` - Processo iniciado
- `🟣 UTMify:` - Processando integração
- `🟡 UTMify:` - Enviando para API
- `✅ UTMify:` - Sucesso
- `❌ UTMify:` - Erro

## Troubleshooting

### PIX não está sendo enviado para UTMify?

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

4. **Teste manualmente:**
   ```bash
   php public/test-utmify-debug.php
   ```

### Erro: "Nenhuma integração ativa encontrada"

- O usuário precisa criar uma integração UTMify em `/integracoes/utmfy`
- A integração deve estar ativa (`is_active = true`)
- O `trigger_on_creation` deve estar habilitado

### Erro: "Gateway não está configurado"

- O usuário precisa ter um gateway configurado
- Verifique em `/admin/gateways` ou `/configuracoes/gateways`

## Exemplo Completo

```bash
# Criar PIX de teste
curl -X POST http://localhost:8000/api/test-pix-simple \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 2,
    "amount": 10.00
  }'

# Verificar logs
tail -f storage/logs/laravel.log | grep -E "UTMify:|TransactionObserver:"

# Verificar integração UTMify
php public/test-utmify-debug.php
```

## Notas

- Apenas transações PIX são enviadas para UTMify
- Apenas status `pending`, `paid` ou `refunded` são enviados
- Apenas integrações do usuário específico são usadas (não globais)
- O Observer é executado automaticamente quando uma transação é criada
- O envio para UTMify é feito de forma assíncrona (não bloqueia a criação do PIX)

