# 📚 API PIX - Documentação Completa e Simples

## 🎯 O Que Você Precisa

1. **Public Key** (PB-playpayments-...) - Chave pública
2. **Private Key** (SK-playpayments-...) - Chave secreta (Secret Key)
3. **URL da API** - Exemplo: `https://seu-dominio.com/api/v1/transactions`

---

## 🔥 Endpoints Principais

### 1️⃣ Criar PIX (Gerar QR Code)

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

### 2️⃣ Consultar Status do PIX

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

## 📋 Campos Importantes

### Para Criar PIX:

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

## 🚀 Exemplo Rápido cURL

### Criar PIX:
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

### Consultar Status:
```bash
curl -X GET "https://seu-dominio.com/api/v1/transactions/TXN_ABC123" \
  -H "Authorization: Bearer SK-playpayments-sua-chave"
```

---

## ⚠️ Erros Comuns

### Erro 401 - Não Autorizado
```
Verifique se as chaves estão corretas
Para criar PIX precisa de AMBAS as chaves (Public + Private)
```

### Erro 422 - Dados Inválidos
```
Verifique se todos os campos obrigatórios foram preenchidos
Email deve ser válido
Documento deve ter pelo menos 11 dígitos
```

### Erro 400 - Gateway Não Configurado
```
O usuário não tem gateway de pagamento configurado
Entre em contato com o suporte
```

---

## 💡 Dicas

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








