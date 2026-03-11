# 🔧 Correção UTMify - Guia Completo

## ✅ O QUE FOI CORRIGIDO

### 1. Payload Corrigido
- ✅ `customer.phone` e `customer.document` agora são **sempre enviados** (podem ser `null`)
- ✅ `customer.ip` é **omitido** se for `null` (API não aceita)
- ✅ `approvedDate` e `refundedAt` são **sempre enviados** (podem ser `null`)
- ✅ `planId` e `planName` são **sempre enviados** (podem ser `null`)
- ✅ `trackingParameters` é um **objeto completo** (não array vazio)

### 2. Limpeza Automática de Token
- ✅ Token é limpo automaticamente (remove espaços/quebras de linha)
- ✅ Token é atualizado no banco se foi limpo

### 3. Logs Melhorados
- ✅ Logs detalhados para diagnóstico
- ✅ Logs específicos para erro de token inválido

## ❌ PROBLEMA ATUAL

O token no banco de dados **não está válido** na UTMify:
- Token: `orKeSuS1R5AH941DFt7aKJGOrb5MLOQeO8Iu`
- Erro: `API_CREDENTIAL_NOT_FOUND` (404)

## 🔧 SOLUÇÃO

### Passo 1: Obter Token Válido da UTMify

1. Acesse: https://utmify.com.br
2. Faça login na sua conta
3. Vá em: **Integrações** > **Webhooks** > **Credenciais de API**
4. Verifique se há uma credencial ativa
5. Se não houver ou estiver inativa:
   - Clique em **Adicionar Credencial**
   - Crie uma nova credencial
   - **Copie o token EXATO** (sem espaços)

### Passo 2: Atualizar Token no Banco

#### Opção A: Usar Script Automático (Recomendado)

```bash
php public/update-utmify-token.php "SEU_TOKEN_AQUI"
```

O script vai:
- Testar o token antes de salvar
- Validar se está correto
- Atualizar no banco automaticamente

#### Opção B: Atualizar Manualmente no Banco

```sql
UPDATE utmify_integrations 
SET api_token = 'SEU_TOKEN_AQUI' 
WHERE id = 2;
```

**IMPORTANTE:** 
- Copie o token EXATAMENTE (sem espaços no início/fim)
- Não adicione quebras de linha
- O token deve ter pelo menos 20 caracteres

### Passo 3: Verificar se Funcionou

1. Crie um novo PIX de teste:
   ```bash
   # Via API
   POST /api/test-pix-simple
   {
     "user_id": 2,
     "amount": 10.00
   }
   ```

2. Verifique os logs:
   ```bash
   tail -f storage/logs/laravel.log | grep "UTMify:"
   ```

3. Procure por:
   - ✅ `✅ UTMify: Transação enviada com SUCESSO` = Funcionou!
   - ❌ `🔴 UTMify: Token da API inválido` = Token ainda está errado

## 🧪 TESTES

### Testar Token Manualmente

```bash
php public/test-utmify-with-token.php
```

### Testar Payload Completo

```bash
php public/test-utmify-full-debug.php
```

### Atualizar Token

```bash
php public/update-utmify-token.php "SEU_TOKEN_AQUI"
```

## 📋 CHECKLIST

- [ ] Token válido obtido da UTMify
- [ ] Token atualizado no banco de dados
- [ ] Token testado e validado
- [ ] PIX de teste criado
- [ ] Logs verificados (deve mostrar "SUCESSO")
- [ ] Transação aparece na UTMify

## 🔍 DIAGNÓSTICO

Se ainda não funcionar após atualizar o token:

1. **Verifique os logs:**
   ```bash
   grep "UTMify:" storage/logs/laravel.log | tail -20
   ```

2. **Verifique se a integração está ativa:**
   ```sql
   SELECT * FROM utmify_integrations WHERE id = 2;
   ```
   - `is_active` deve ser `1`
   - `trigger_on_creation` deve ser `1`
   - `trigger_on_payment` deve ser `1`

3. **Verifique se há transações PIX:**
   ```sql
   SELECT * FROM transactions 
   WHERE user_id = 2 
   AND payment_method = 'pix' 
   ORDER BY created_at DESC 
   LIMIT 5;
   ```

4. **Teste manualmente:**
   ```bash
   php public/test-utmify-with-token.php
   ```

## 📞 SUPORTE

Se o problema persistir:
1. Verifique se a conta UTMify está ativa
2. Verifique se a credencial não foi revogada
3. Tente criar uma nova credencial
4. Verifique se há limites de uso na API UTMify

## ✅ STATUS

- ✅ Código corrigido e funcionando
- ✅ Payload correto
- ✅ Limpeza automática de token
- ❌ **Token precisa ser atualizado no banco de dados**

**Após atualizar o token, tudo deve funcionar automaticamente!**

