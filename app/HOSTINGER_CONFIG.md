# Configuração do Banco de Dados na Hostinger

## Erro Comum
```
SQLSTATE[HY000] [1045] Access denied for user 'u999974013_playpayments'@'localhost' (using password: YES)
```

## Solução

### 1. Obter Credenciais do Banco de Dados

1. Acesse o **Painel de Controle da Hostinger** (hpanel.hostinger.com)
2. Vá em **Banco de Dados MySQL**
3. Encontre seu banco de dados e clique em **Gerenciar**
4. Anote as seguintes informações:
   - **Nome do Banco de Dados**: geralmente no formato `u999974013_playpayments`
   - **Usuário do Banco**: geralmente no formato `u999974013_playpayments`
   - **Senha do Banco**: a senha que você configurou
   - **Host do Banco**: geralmente `localhost` (na Hostinger)

### 2. Configurar o arquivo `.env`

No servidor da Hostinger, edite o arquivo `.env` na raiz do projeto com as seguintes configurações:

```env
APP_NAME=playpayments
APP_ENV=production
APP_KEY=base64:SUA_APP_KEY_AQUI
APP_DEBUG=false
APP_URL=https://ganhadinheiro.site

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u999974013_playpayments
DB_USERNAME=u999974013_playpayments
DB_PASSWORD=SUA_SENHA_DO_BANCO_AQUI
```

**⚠️ IMPORTANTE:**
- Substitua `u999974013_playpayments` pelos seus dados reais
- Substitua `SUA_SENHA_DO_BANCO_AQUI` pela senha real do banco
- Na Hostinger, o `DB_HOST` geralmente é `localhost`
- Se a porta não for 3306, ajuste o `DB_PORT`

### 3. Verificar Permissões do Usuário

1. No painel da Hostinger, vá em **Banco de Dados MySQL**
2. Clique em **Gerenciar** no seu banco
3. Verifique se o usuário tem permissões para:
   - SELECT
   - INSERT
   - UPDATE
   - DELETE
   - CREATE
   - ALTER
   - DROP
   - INDEX

### 4. Testar Conexão

Após configurar o `.env`, execute no terminal SSH da Hostinger:

```bash
cd public_html  # ou o diretório onde está seu projeto
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### 5. Executar Migrations

Depois de confirmar a conexão:

```bash
php artisan migrate --force
```

### 6. Verificar Configuração

Crie um arquivo temporário `test-connection.php` na pasta `public`:

```php
<?php
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    DB::connection()->getPdo();
    echo "✅ Conexão com o banco de dados estabelecida com sucesso!";
} catch (\Exception $e) {
    echo "❌ Erro de conexão: " . $e->getMessage();
}
```

Acesse: `https://ganhadinheiro.site/test-connection.php`

**⚠️ LEMBRE-SE:** Após testar, delete o arquivo `test-connection.php` por segurança!

## Problemas Comuns

### Erro: "Access denied"
- Verifique se a senha está correta no `.env`
- Verifique se o usuário tem permissões no banco
- Verifique se o nome do banco está correto

### Erro: "Unknown database"
- Verifique se o banco de dados foi criado no painel da Hostinger
- Verifique se o nome do banco está correto no `.env`

### Erro: "Can't connect to MySQL server"
- Verifique se o `DB_HOST` está correto (geralmente `localhost` na Hostinger)
- Verifique se a porta está correta (geralmente `3306`)
- Verifique se o serviço MySQL está ativo

## Notas Importantes

1. **Nunca commite o arquivo `.env`** no Git
2. O arquivo `.env` deve estar na **raiz do projeto** (mesmo nível que `composer.json`)
3. Na Hostinger, o documento root geralmente é a pasta `public_html`, então você precisa ajustar o `.htaccess` se necessário
4. Sempre use `APP_DEBUG=false` em produção
5. Gere uma nova `APP_KEY` se necessário: `php artisan key:generate`

## Gerar APP_KEY

Se você não tem uma `APP_KEY`, gere uma nova:

```bash
php artisan key:generate --force
```

Isso irá atualizar o `.env` automaticamente.

