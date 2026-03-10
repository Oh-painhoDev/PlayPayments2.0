# Diagnóstico do Gateway Pluggou

## Problemas Comuns do Gateway Pluggou

### Problema 1: "Gateway não está configurado com credenciais válidas"
### Problema 2: "Erro na API Pluggou (HTTP 401): Chave não encontrada ou inativa"

Este documento ajuda a diagnosticar e resolver problemas de configuração do gateway Pluggou.

## Passo 1: Verificar se o Gateway está Criado

1. Acesse o painel administrativo: `/admin/gateways`
2. Verifique se o gateway "Pluggou" existe na lista
3. Verifique se o gateway está **ativo** (is_active = true)

## Passo 2: Verificar se há um Usuário Admin

O sistema precisa de um usuário com `role = 'admin'` para armazenar as credenciais do gateway.

**Verificar no banco de dados:**
```sql
SELECT id, name, email, role FROM users WHERE role = 'admin';
```

**Ou via Tinker:**
```bash
php artisan tinker
```
```php
$admin = \App\Models\User::where('role', 'admin')->first();
if ($admin) {
    echo "Admin encontrado: " . $admin->name . " (" . $admin->email . ")\n";
} else {
    echo "ERRO: Nenhum usuário admin encontrado!\n";
}
```

## Passo 3: Configurar as Credenciais

1. Acesse: `/admin/gateways`
2. Clique em **"Configurar"** no gateway Pluggou
3. Preencha os campos:
   - **Public Key**: Sua chave pública do Pluggou (obrigatória)
   - **Secret Key**: Sua chave secreta do Pluggou (obrigatória)
   - **Sandbox**: Marque se estiver usando ambiente de teste
4. Clique em **"Salvar"**

## Passo 4: Verificar se as Credenciais foram Salvas

**Via Tinker:**
```bash
php artisan tinker
```
```php
$gateway = \App\Models\PaymentGateway::where('slug', 'pluggou')->orWhere('name', 'like', '%pluggou%')->first();
if ($gateway) {
    echo "Gateway encontrado: " . $gateway->name . " (ID: " . $gateway->id . ")\n";
    echo "Gateway ativo: " . ($gateway->is_active ? 'Sim' : 'Não') . "\n";
    
    $admin = \App\Models\User::where('role', 'admin')->first();
    if ($admin) {
        $cred = \App\Models\UserGatewayCredential::where('user_id', $admin->id)
            ->where('gateway_id', $gateway->id)
            ->first();
        
        if ($cred) {
            echo "Credenciais encontradas:\n";
            echo "  - ID: " . $cred->id . "\n";
            echo "  - Ativa: " . ($cred->is_active ? 'Sim' : 'Não') . "\n";
            echo "  - Sandbox: " . ($cred->is_sandbox ? 'Sim' : 'Não') . "\n";
            echo "  - Public Key existe: " . (!empty($cred->public_key) ? 'Sim' : 'Não') . "\n";
            echo "  - Public Key length: " . (strlen($cred->public_key ?? '')) . "\n";
            echo "  - Public Key preview: " . substr($cred->public_key ?? '', 0, 10) . "...\n";
            
            try {
                $secretKey = $cred->secret_key;
                echo "  - Secret Key descriptografada: " . (!empty($secretKey) ? 'Sim' : 'Não') . "\n";
                echo "  - Secret Key length: " . (strlen($secretKey ?? '')) . "\n";
                echo "  - Secret Key preview: " . substr($secretKey ?? '', 0, 10) . "...\n";
            } catch (\Exception $e) {
                echo "  - ERRO ao descriptografar Secret Key: " . $e->getMessage() . "\n";
            }
        } else {
            echo "ERRO: Credenciais não encontradas para o admin user!\n";
        }
    } else {
        echo "ERRO: Nenhum usuário admin encontrado!\n";
    }
} else {
    echo "ERRO: Gateway Pluggou não encontrado!\n";
}
```

## Passo 5: Verificar os Logs

Os logs do Laravel contêm informações detalhadas sobre o processo de configuração e uso do gateway.

**Verificar logs recentes:**
```bash
tail -n 100 storage/logs/laravel.log | grep -i "pluggou\|gateway\|credential"
```

**Ou via Tinker:**
```bash
php artisan tinker
```
```php
// Verificar últimos logs relacionados ao Pluggou
$logs = \Illuminate\Support\Facades\DB::table('logs')
    ->where('message', 'like', '%pluggou%')
    ->orWhere('message', 'like', '%gateway%')
    ->orWhere('message', 'like', '%credential%')
    ->orderBy('created_at', 'desc')
    ->limit(20)
    ->get();
    
foreach ($logs as $log) {
    echo "[" . $log->created_at . "] " . $log->level . ": " . $log->message . "\n";
}
```

## Passo 6: Testar a Conexão

Após configurar as credenciais, você pode testar a conexão diretamente no painel administrativo:

1. Acesse: `/admin/gateways`
2. Clique em **"Testar Conexão"** no gateway Pluggou
3. Verifique se a mensagem de sucesso aparece

## Passo 7: Verificar se o Gateway está Atribuído ao Usuário

Para que um usuário possa gerar PIX, ele precisa ter um gateway atribuído:

**Verificar via Tinker:**
```bash
php artisan tinker
```
```php
$user = \App\Models\User::find(3); // Substitua 3 pelo ID do usuário
if ($user) {
    echo "Usuário: " . $user->name . " (" . $user->email . ")\n";
    echo "Gateway atribuído: " . ($user->assignedGateway ? $user->assignedGateway->name : 'Nenhum') . "\n";
    if ($user->assignedGateway) {
        echo "Gateway ativo: " . ($user->assignedGateway->is_active ? 'Sim' : 'Não') . "\n";
    }
} else {
    echo "Usuário não encontrado!\n";
}
```

## Problemas Comuns e Soluções

### Problema 1: "Admin user not found"
**Solução:** Crie um usuário com `role = 'admin'` no banco de dados.

### Problema 2: "Gateway credentials not found"
**Solução:** Configure as credenciais do gateway no painel administrativo (`/admin/gateways`).

### Problema 3: "Gateway credentials are inactive"
**Solução:** Ative as credenciais no painel administrativo ou verifique se `is_active = true` no banco de dados.

### Problema 4: "Public Key é obrigatória para o gateway Pluggou"
**Solução:** Certifique-se de preencher o campo "Public Key" ao configurar o gateway Pluggou.

### Problema 5: "Secret Key é obrigatória"
**Solução:** Certifique-se de preencher o campo "Secret Key" ao configurar o gateway.

### Problema 6: "Error decrypting secret key"
**Solução:** 
- Verifique se a `APP_KEY` no arquivo `.env` está configurada corretamente
- Se a `APP_KEY` foi alterada após salvar as credenciais, você precisará reconfigurar as credenciais
- Execute: `php artisan key:generate` para gerar uma nova chave (isso invalidará credenciais antigas)

### Problema 7: "Gateway não está atribuído ao usuário"
**Solução:** Atribua um gateway ao usuário no painel administrativo ou via banco de dados.

### Problema 8: "Erro na API Pluggou (HTTP 401): Chave não encontrada ou inativa" ⚠️

Este é um erro de autenticação retornado pela API Pluggou. Possíveis causas:

1. **Credenciais incorretas**
   - Verifique se a Public Key e Secret Key estão corretas
   - Certifique-se de copiar as credenciais corretamente (sem espaços extras)
   - Reconfigure as credenciais no painel administrativo

2. **Credenciais inativas no painel da Pluggou**
   - Acesse o painel da Pluggou e verifique se as chaves estão ativas
   - Ative as chaves se estiverem inativas
   - Verifique se as chaves não expiraram

3. **Credenciais sem permissões adequadas**
   - Verifique no painel da Pluggou se as chaves têm permissão para criar transações PIX
   - Contate o suporte da Pluggou para verificar as permissões

4. **Ambiente incorreto (sandbox vs produção)**
   - Verifique se está usando credenciais do ambiente correto
   - Se estiver em produção, use credenciais de produção
   - Se estiver em teste, use credenciais de sandbox
   - Verifique a opção "Sandbox" ao configurar as credenciais

5. **Credenciais com caracteres especiais ou espaços**
   - O sistema agora remove automaticamente espaços e caracteres especiais
   - Se o problema persistir, reconfigurar as credenciais no painel administrativo
   - Certifique-se de copiar as credenciais sem espaços extras

**Soluções:**
1. **Reconfigurar as credenciais:**
   - Acesse `/admin/gateways`
   - Clique em "Configurar" no gateway Pluggou
   - Cole as credenciais novamente (certifique-se de não ter espaços extras)
   - Marque "Sandbox" se estiver usando ambiente de teste
   - Salve e teste a conexão

2. **Verificar no painel da Pluggou:**
   - Acesse o painel da Pluggou
   - Verifique se as chaves estão ativas
   - Verifique as permissões das chaves
   - Gere novas chaves se necessário

3. **Testar as credenciais:**
   - Execute o script de teste: `http://seu-dominio.com/test-pluggou-credentials.php`
   - Este script testa se as credenciais estão configuradas corretamente e se conseguem se autenticar na API

4. **Verificar os logs:**
   - Verifique os logs do Laravel para mais detalhes: `storage/logs/laravel.log`
   - Procure por mensagens relacionadas ao Pluggou
   - Verifique se há informações sobre o erro 401

5. **Contatar o suporte da Pluggou:**
   - Se nenhuma das soluções acima funcionar, contate o suporte da Pluggou
   - Forneça as informações dos logs
   - Verifique se há algum problema conhecido com a API

## Scripts de Diagnóstico

### Script 1: Verificação Básica

Execute o script de diagnóstico básico:

```bash
php public/check-gateway-credentials.php
```

Este script verifica automaticamente:
- Se o gateway Pluggou existe
- Se o gateway está ativo
- Se há um usuário admin
- Se as credenciais existem
- Se as credenciais estão ativas
- Se a Public Key e Secret Key estão preenchidas

### Script 2: Teste de Autenticação (Recomendado para erro 401)

Execute o script de teste de autenticação:

```bash
# Acesse no navegador:
http://seu-dominio.com/test-pluggou-credentials.php
```

Este script testa:
- Se as credenciais estão configuradas corretamente
- Se as credenciais conseguem se autenticar na API Pluggou
- Se há problemas com a descriptografia das credenciais
- Se a URL da API está correta
- Se os headers estão sendo enviados corretamente

**Como usar:**
1. Acesse a URL no navegador
2. O script mostrará um relatório detalhado
3. Verifique se a autenticação foi bem-sucedida
4. Se falhar, verifique as possíveis causas listadas no relatório

## Contato com Suporte

Se o problema persistir após seguir todos os passos acima, entre em contato com o suporte e forneça:
1. Logs do Laravel (`storage/logs/laravel.log`)
2. Resultado do script de diagnóstico
3. Screenshot da página de configuração do gateway
4. Informações sobre quando o problema começou a ocorrer

