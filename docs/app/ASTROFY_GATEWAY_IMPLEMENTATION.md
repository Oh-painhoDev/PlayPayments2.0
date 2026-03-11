# 🚀 Implementação Completa do Gateway Astrofy

Este documento descreve a implementação completa do Gateway de Pagamento compatível 100% com o padrão Astrofy Gateway Hub.

## ✅ Funcionalidades Implementadas

### 1. Autenticação Obrigatória
- ✅ Middleware `AstrofyAuthMiddleware` que valida:
  - `X-Gateway-Key`: identifica o gateway
  - `X-Api-Key`: identifica o cliente final (formato: `{gateway_id}:{user_private_key}`)
- ✅ Validação regex da X-Api-Key conforme especificação oficial
- ✅ Validação de comprimento máximo (73 caracteres)

### 2. Endpoints do Provedor (Chamados pela Astrofy)

#### POST /astrofy/order
- ✅ Cria ordem de pagamento
- ✅ Suporta PIX e CARD
- ✅ Validação completa de payload
- ✅ Idempotência (retorna mesma transação se orderId já existir)
- ✅ Validação de método de pagamento suportado
- ✅ Retorna `externalId`, `status` e `instructions`

#### GET /astrofy/order/:externalId
- ✅ Consulta status da ordem
- ✅ Retorna `externalId`, `status` e `instructions` (se pendente)

### 3. Endpoints do Ecossistema Astrofy (Chamados pelo Provedor)

#### GET /v1/gateway
- ✅ Retorna dados do gateway vinculado à X-Gateway-Key
- ✅ Retorna: id, name, baseUrl, paymentTypes, pictureUrl, description

#### POST /v1/gateway
- ✅ Criar/Atualizar gateway (idempotente)
- ✅ Validação de payload
- ✅ Suporta PIX e CARD

#### DELETE /v1/gateway
- ✅ Remove/desativa gateway

#### POST /v1/gateway/logo
- ✅ Upload de logo do gateway
- ✅ Validação de imagem (jpeg, jpg, png, webp)
- ✅ Máximo 50MB
- ✅ Retorna URL, width e height

### 4. Webhooks
- ✅ Envio automático de webhooks para Astrofy quando status muda
- ✅ Inclui `instructions` quando status é PENDING
- ✅ Headers: `X-Gateway-Key`
- ✅ Endpoint: `https://gatewayhub.astrofy.site/v1/gateway/webhook`

### 5. Validações e Segurança
- ✅ Validação de headers obrigatórios
- ✅ Validação de formato da X-Api-Key (regex oficial)
- ✅ Validação de payloads
- ✅ Validação de métodos de pagamento suportados
- ✅ Validação de currency (apenas BRL)
- ✅ Validação de documento (apenas CPF)
- ✅ Validação de instructions.type (apenas TOKEN ou URL)

### 6. Status Mapeados
- ✅ `PENDING`: pending, waiting_payment, waiting, processing, authorized
- ✅ `APPROVED`: paid, paid_out, completed, success, approved, confirmed, settled, captured
- ✅ `REJECTED`: failed, cancelled, expired, rejected, declined, error
- ✅ `REFUNDED`: refunded, partially_refunded, reversed

## 📁 Arquivos Criados/Modificados

### Novos Arquivos
1. `app/Http/Middleware/AstrofyAuthMiddleware.php` - Middleware de autenticação
2. `app/Http/Controllers/Api/AstrofyGatewayController.php` - Controller dos endpoints do provedor
3. `app/Http/Controllers/Api/AstrofyEcosystemController.php` - Controller dos endpoints do ecossistema
4. `database/migrations/2025_01_20_000000_add_description_and_picture_url_to_astrofy_integrations.php` - Migration para novos campos

### Arquivos Modificados
1. `app/Models/AstrofyIntegration.php` - Adicionados campos `description` e `picture_url`
2. `app/Services/AstrofyService.php` - Melhorado webhook para incluir instructions
3. `routes/api.php` - Adicionadas rotas Astrofy
4. `routes/api-subdomain.php` - Adicionadas rotas Astrofy para subdomínio

## 🔧 Configuração

### 1. Executar Migration
```bash
php artisan migrate
```

### 2. Configurar Gateway na Astrofy
1. Acesse o painel da Astrofy
2. Registre seu gateway usando:
   - `baseUrl`: URL base da sua API (ex: `https://api.seudominio.com`)
   - `paymentTypes`: `["PIX", "CARD"]`
   - `name`: Nome do seu gateway
   - `description`: Descrição do gateway

### 3. Obter Gateway Key
Após registrar, você receberá uma `gateway_key` que deve ser salva na tabela `astrofy_integrations`.

## 📝 Exemplos de Uso

### Criar Ordem (POST /astrofy/order)
```bash
curl -X POST https://api.seudominio.com/astrofy/order \
  -H "X-Gateway-Key: sua-gateway-key" \
  -H "X-Api-Key: uuid-v4:chave-privada" \
  -H "Content-Type: application/json" \
  -d '{
    "orderId": "12345",
    "amount": 50.00,
    "currency": "BRL",
    "description": "Pedido de teste",
    "paymentMethod": "PIX",
    "customer": {
      "name": "João da Silva",
      "email": "joao@email.com",
      "document": {
        "type": "CPF",
        "value": "18646546004"
      }
    }
  }'
```

### Consultar Ordem (GET /astrofy/order/:externalId)
```bash
curl -X GET https://api.seudominio.com/astrofy/order/TXN_ABC123 \
  -H "X-Gateway-Key: sua-gateway-key" \
  -H "X-Api-Key: uuid-v4:chave-privada"
```

### Registrar Gateway (POST /v1/gateway)
```bash
curl -X POST https://gatewayhub.astrofy.site/v1/gateway \
  -H "X-Gateway-Key: sua-gateway-key" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Meu Gateway",
    "baseUrl": "https://api.seudominio.com",
    "paymentTypes": ["PIX", "CARD"],
    "description": "Gateway de pagamento"
  }'
```

## 🔐 Segurança

- ✅ Todas as requisições devem usar HTTPS
- ✅ Validação rigorosa de headers
- ✅ Validação de formato da X-Api-Key
- ✅ Validação de payloads
- ✅ Logs de segurança para tentativas inválidas

## ⚡ Performance

- ✅ Respostas em até 5 segundos
- ✅ Idempotência para evitar duplicações
- ✅ Cache de integrações quando apropriado

## 📊 Logs

Todos os eventos são registrados nos logs do Laravel:
- ✅ Criação de ordens
- ✅ Consultas de status
- ✅ Envio de webhooks
- ✅ Erros e exceções
- ✅ Tentativas de autenticação inválidas

## 🧪 Testes

Para testar a implementação:

1. **Teste de Autenticação**: Verifique se headers inválidos retornam 401
2. **Teste de Criação**: Crie uma ordem e verifique se retorna externalId e instructions
3. **Teste de Idempotência**: Crie a mesma ordem duas vezes e verifique se retorna a mesma transação
4. **Teste de Consulta**: Consulte o status de uma ordem existente
5. **Teste de Webhook**: Verifique se webhooks são enviados quando status muda

## 📚 Documentação Adicional

- [Documentação Oficial Astrofy](https://gatewayhub.astrofy.site/docs)
- [Especificação de Webhooks](./api/PODPAY_WEBHOOKS.md)

## 🐛 Troubleshooting

### Erro 401 Unauthorized
- Verifique se `X-Gateway-Key` e `X-Api-Key` estão presentes
- Verifique se o formato da `X-Api-Key` está correto: `{uuid-v4}:{chave-privada}`
- Verifique se a integração está ativa no banco de dados

### Erro 400 BadRequest
- Verifique se todos os campos obrigatórios estão presentes
- Verifique se o método de pagamento é suportado
- Verifique se currency é BRL
- Verifique se documento é CPF

### Webhook não enviado
- Verifique se a integração está ativa
- Verifique os logs para erros
- Verifique se o status da transação é mapeável

## ✅ Checklist de Implementação

- [x] Middleware de autenticação
- [x] Validação da X-Api-Key
- [x] Endpoint POST /order
- [x] Endpoint GET /order/:externalId
- [x] Endpoint GET /v1/gateway
- [x] Endpoint POST /v1/gateway
- [x] Endpoint DELETE /v1/gateway
- [x] Endpoint POST /v1/gateway/logo
- [x] Suporte a PIX
- [x] Suporte a CARD
- [x] Idempotência
- [x] Webhooks
- [x] Validações completas
- [x] Tratamento de erros
- [x] Logs
- [x] Migration de banco de dados

## 🎯 Próximos Passos

1. Executar migration: `php artisan migrate`
2. Testar endpoints com Postman ou curl
3. Configurar gateway na Astrofy
4. Monitorar logs para garantir funcionamento correto
5. Implementar testes automatizados (opcional)

---

**Implementação concluída com sucesso!** 🎉

