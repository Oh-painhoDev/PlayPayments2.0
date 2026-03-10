# ✅ Campo Platform Name Adicionado

## 📋 O que foi feito:

1. **Migration criada e executada**: Campo `platform_name` adicionado à tabela `utmify_integrations`
2. **Modelo atualizado**: `UtmifyIntegration` agora inclui `platform_name` no `$fillable`
3. **Serviço atualizado**: `UtmifyService` agora:
   - Usa `platform_name` da integração se configurado
   - Tenta `config('app.utmify_platform_name')` se não houver na integração
   - Tenta `config('app.name')` convertido para PascalCase
   - Usa "playpayments" como padrão se nada estiver configurado
4. **Controllers atualizados**: Tanto `UtmifyController` quanto `Admin\UtmifyController` agora salvam/atualizam `platform_name`
5. **View atualizada**: Campo `platform_name` adicionado ao formulário de criação/edição
6. **JavaScript atualizado**: Campo `platform_name` é carregado ao editar uma integração
7. **Banco atualizado**: Integração ID 2 agora tem `platform_name = 'GlobalPay'`

## 🎯 Como usar:

### Para usuários (via interface):
1. Acesse `/integracoes/utmfy`
2. Crie ou edite uma integração
3. No campo "Nome da Plataforma", informe o nome desejado (ex: `GlobalPay`, `playpayments`, `MeuNegocio`)
4. Se deixar em branco, será usado "playpayments" por padrão

### Para admin (via interface):
1. Acesse `/admin/white-label/utmify`
2. Crie ou edite uma integração
3. No campo "Nome da Plataforma", informe o nome desejado

### Via banco de dados:
```sql
UPDATE utmify_integrations 
SET platform_name = 'GlobalPay' 
WHERE id = 2;
```

### Via configuração (config/app.php):
```php
'utmify_platform_name' => 'GlobalPay',
```

## 📊 Status atual:

- ✅ Integração ID 2: `platform_name = 'GlobalPay'`
- ✅ Código atualizado e funcionando
- ✅ Pronto para testar

## ⚠️ IMPORTANTE:

**O problema principal continua sendo o token inválido (404 API_CREDENTIAL_NOT_FOUND).**

O nome da plataforma agora está configurado como "GlobalPay" (igual à documentação), mas se o token ainda estiver inválido, o erro 404 continuará ocorrendo.

## 🧪 Teste:

1. Crie um novo PIX de teste: `http://localhost:8000/test-pix-api.php`
2. Verifique os logs para ver o nome da plataforma usado
3. Se o token estiver válido, o envio deve funcionar com `platform: "GlobalPay"`

## 📝 Próximos passos:

1. **Atualizar o token da API UTMify** (principal problema)
2. Testar com o novo nome da plataforma
3. Verificar se a API UTMify aceita "GlobalPay" como nome da plataforma

---

**Nota**: O código agora está completo e funcional. O campo `platform_name` pode ser configurado de várias formas, e a integração ID 2 já está usando "GlobalPay" como na documentação.

