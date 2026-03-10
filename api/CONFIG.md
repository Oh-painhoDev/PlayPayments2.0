# Configuração do Subdomínio API

## Passo a Passo para Configurar o Subdomínio

### 1. Configurar o arquivo hosts (Windows)

Edite o arquivo `C:\Windows\System32\drivers\etc\hosts` (como administrador) e adicione:

```
127.0.0.1 api.localhost
```

### 2. Configurar o Apache (XAMPP)

Edite o arquivo `C:\xampp\apache\conf\extra\httpd-vhosts.conf` e adicione:

```apache
<VirtualHost *:8000>
    ServerName api.localhost
    DocumentRoot "C:/xampp/htdocs/public"
    
    <Directory "C:/xampp/htdocs/public">
        AllowOverride All
        Require all granted
        Options Indexes FollowSymLinks
    </Directory>
    
    ErrorLog "C:/xampp/apache/logs/api.localhost-error.log"
    CustomLog "C:/xampp/apache/logs/api.localhost-access.log" common
</VirtualHost>
```

**IMPORTANTE**: Certifique-se de que a porta 8000 está configurada no `httpd.conf`:

```apache
Listen 8000
```

### 3. Reiniciar o Apache

Reinicie o Apache no XAMPP Control Panel.

### 4. Testar

Acesse no navegador:
- `http://api.localhost:8000/` → Deve redirecionar para `/acessar`
- `http://api.localhost:8000/acessar` → Deve retornar JSON
- `http://api.localhost:8000/health` → Deve retornar JSON com status

### 5. Verificar Rotas

Execute no terminal:
```bash
php artisan route:list --path=api
```

## Troubleshooting

### Erro: "This site can't be reached"

- Verifique se o arquivo `hosts` foi salvo corretamente
- Verifique se o Apache está rodando na porta 8000
- Tente limpar o cache do DNS: `ipconfig /flushdns` (Windows)

### Erro: "403 Forbidden"

- Verifique as permissões do diretório `public`
- Verifique se `AllowOverride All` está configurado
- Verifique os logs do Apache em `C:/xampp/apache/logs/`

### Erro: "404 Not Found"

- Verifique se o `DocumentRoot` está apontando para a pasta `public`
- Verifique se o arquivo `.htaccess` está na pasta `public`
- Verifique se o módulo `mod_rewrite` está habilitado

## Produção

Para produção, configure um subdomínio real (ex: `api.dominio.com`) e atualize:

1. DNS - Crie um registro A apontando para o IP do servidor
2. Servidor Web - Configure o VirtualHost/Server Block
3. SSL - Configure um certificado SSL (Let's Encrypt recomendado)
4. `.env` - Atualize `APP_URL` se necessário

