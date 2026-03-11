# 🐊 Teste Completo - SharkBanking Integration

## 📋 Como Usar

### Método 1: Usando o Comando Artisan com Opções

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

### Método 2: Usando o Comando Interativo

```bash
php artisan test:sharkbanking --user_id=1
```

O comando irá perguntar os dados que faltarem.

---

## 🔧 Parâmetros Disponíveis

### Configurações Básicas

| Parâmetro | Tipo | Obrigatório | Padrão | Descrição |
|-----------|------|-------------|--------|-----------|
| `--user_id` | integer | ✅ Sim | - | ID do usuário |
| `--gateway_id` | integer | ❌ Não | Gateway do usuário | ID do gateway (opcional) |
| `--amount` | float | ❌ Não | 100.00 | Valor da transação |
| `--payment_method` | string | ❌ Não | pix | Método: `pix`, `credit_card`, `bank_slip` |

### Informações do Produto/Venda

| Parâmetro | Tipo | Obrigatório | Padrão | Descrição |
|-----------|------|-------------|--------|-----------|
| `--sale_name` | string | ❌ Não | "Produto Teste" | **Nome da venda/produto** |
| `--description` | string | ❌ Não | "Descrição do produto..." | **Descrição detalhada** |

### Configurações PIX

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

### Dados do Cliente

| Parâmetro | Tipo | Obrigatório | Padrão | Descrição |
|-----------|------|-------------|--------|-----------|
| `--customer_name` | string | ❌ Não | "João Silva" | Nome completo |
| `--customer_email` | string | ❌ Não | "joao@example.com" | Email |
| `--customer_document` | string | ❌ Não | "12345678900" | CPF/CNPJ (apenas números) |
| `--customer_phone` | string | ❌ Não | "11999999999" | Telefone (apenas números) |

---

## 📝 Exemplos Práticos

### Exemplo 1: PIX com 15 minutos

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

### Exemplo 2: PIX com 7 dias

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

### Exemplo 3: PIX com 30 dias

```bash
php artisan test:sharkbanking \
    --user_id=1 \
    --amount=500.00 \
    --payment_method=pix \
    --sale_name="Produto Premium" \
    --description="Licença anual do software premium" \
    --pix_expires_in_days=30 \
    --customer_name="Ana Costa" \
    --customer_email="ana@example.com" \
    --customer_document="55566677788" \
    --customer_phone="11966666666"
```

### Exemplo 4: PIX com 90 dias (máximo)

```bash
php artisan test:sharkbanking \
    --user_id=1 \
    --amount=1000.00 \
    --payment_method=pix \
    --sale_name="Licença Anual" \
    --description="Licença completa por 12 meses" \
    --pix_expires_in_days=90 \
    --customer_name="Pedro Almeida" \
    --customer_email="pedro@example.com" \
    --customer_document="99988877766" \
    --customer_phone="11955555555"
```

### Exemplo 5: Cartão de Crédito

```bash
php artisan test:sharkbanking \
    --user_id=1 \
    --amount=300.00 \
    --payment_method=credit_card \
    --sale_name="Combo Premium" \
    --description="Pacote completo com todos os recursos" \
    --customer_name="Lucia Ferreira" \
    --customer_email="lucia@example.com" \
    --customer_document="44455566677" \
    --customer_phone="11944444444"
```

---

## 🔍 O que o Comando Mostra

Após executar, você verá:

1. **✅ Validação do Usuário e Gateway**
2. **📋 Resumo da Transação** (todos os parâmetros configurados)
3. **🔄 Criação da Transação**
4. **📊 Detalhes da Transação Criada:**
   - ID Interno
   - ID Externo (da API SharkBanking)
   - Valor
   - Método de Pagamento
   - Status
   - Data de Criação
   - Data de Expiração

5. **🔐 Dados do PIX** (se método for PIX):
   - QR Code
   - Payload (Copia e Cola)
   - Data de Expiração

6. **📡 Resposta Completa do Gateway** (JSON formatado)

---

## ⚙️ Configuração Avançada

### Usando Arquivo de Configuração

1. Edite o arquivo `test_sharkbanking.php`
2. Configure a variável `$CONFIG` com seus valores
3. Execute o comando normalmente

### Valores de Expiração PIX

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

## 🐛 Troubleshooting

### Erro: "Usuário não encontrado"
- Verifique se o `user_id` está correto
- Execute: `php artisan tinker` → `User::find(1)`

### Erro: "Gateway não configurado"
- Verifique se o usuário tem um gateway atribuído
- Ou informe `--gateway_id` com o ID do gateway

### Erro: "Erro na API SharkBanking"
- Verifique as credenciais do gateway
- Verifique a URL da API
- Verifique os logs em `storage/logs/laravel.log`

---

## 📚 Referências

- **API SharkBanking:** https://api.sharkbanking.com.br/v1/transactions
- **Documentação:** Veja os comentários no código

---

## ✅ Checklist de Teste

- [ ] ✅ Testar PIX com 15 minutos
- [ ] ✅ Testar PIX com 1 dia
- [ ] ✅ Testar PIX com 7 dias
- [ ] ✅ Testar PIX com 30 dias
- [ ] ✅ Testar PIX com 90 dias (máximo)
- [ ] ✅ Testar com nome de produto personalizado
- [ ] ✅ Testar com descrição personalizada
- [ ] ✅ Verificar QR Code gerado
- [ ] ✅ Verificar Payload (Copia e Cola)
- [ ] ✅ Verificar data de expiração

---

**🎉 Pronto! Agora você pode testar tudo facilmente!**




