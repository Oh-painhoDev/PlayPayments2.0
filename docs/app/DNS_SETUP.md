# Configuração de DNS para Subdomínios

Este guia explica como configurar os subdomínios `api` e `app` no seu provedor de DNS.

## 📋 Registros DNS Necessários

### 1. Subdomínio API (`api.seudominio.com`)

Configure o seguinte registro DNS:

```
Type: A
Name: api
Points to: [IP_DO_SEU_SERVIDOR]
TTL: 14400 (ou o padrão do seu provedor)
```

**Exemplo:**
- Se seu servidor está em `192.0.2.1`, configure:
  - Type: `A`
  - Name: `api`
  - Points to: `192.0.2.1`
  - TTL: `14400`

### 2. Subdomínio App (`app.seudominio.com`)

Configure o seguinte registro DNS:

```
Type: A
Name: app
Points to: [IP_DO_SEU_SERVIDOR]
TTL: 14400 (ou o padrão do seu provedor)
```

**Exemplo:**
- Se seu servidor está em `192.0.2.1`, configure:
  - Type: `A`
  - Name: `app`
  - Points to: `192.0.2.1`
  - TTL: `14400`

### 3. Domínio Principal (Opcional)

Se quiser que o domínio principal também aponte para o app:

```
Type: A
Name: @
Points to: [IP_DO_SEU_SERVIDOR]
TTL: 14400
```

## 🔧 Configuração no Servidor Web

### Apache (httpd-vhosts.conf)

Adicione as seguintes configurações no seu arquivo de VirtualHost:

```apache
# Subdomínio API
<VirtualHost *:80>
    ServerName api.seudominio.com
    DocumentRoot "C:/xampp/htdocs/public"
    
    <Directory "C:/xampp/htdocs/public">
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog "logs/api-error.log"
    CustomLog "logs/api-access.log" common
</VirtualHost>

# Subdomínio App
<VirtualHost *:80>
    ServerName app.seudominio.com
    DocumentRoot "C:/xampp/htdocs/public"
    
    <Directory "C:/xampp/htdocs/public">
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog "logs/app-error.log"
    CustomLog "logs/app-access.log" common
</VirtualHost>

# Domínio Principal (opcional)
<VirtualHost *:80>
    ServerName seudominio.com
    ServerAlias www.seudominio.com
    DocumentRoot "C:/xampp/htdocs/public"
    
    <Directory "C:/xampp/htdocs/public">
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog "logs/error.log"
    CustomLog "logs/access.log" common
</VirtualHost>
```

### Nginx

```nginx
# Subdomínio API
server {
    listen 80;
    server_name api.seudominio.com;
    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# Subdomínio App
server {
    listen 80;
    server_name app.seudominio.com;
    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## ⚙️ Configuração no Laravel

### 1. Arquivo `.env`

Certifique-se de que o `APP_URL` está configurado:

```env
APP_URL=https://app.seudominio.com
```

### 2. Verificar Rotas de Subdomínio

O arquivo `routes/api-subdomain.php` já está configurado para aceitar requisições no subdomínio `api`.

### 3. Testar Configuração

Após configurar o DNS e o servidor web:

1. **Teste o subdomínio API:**
   ```bash
   curl https://api.seudominio.com/health
   ```

2. **Teste o subdomínio App:**
   ```bash
   curl https://app.seudominio.com
   ```

## 🔍 Verificação de DNS

Para verificar se os DNS estão configurados corretamente:

### Windows (PowerShell)
```powershell
nslookup api.seudominio.com
nslookup app.seudominio.com
```

### Linux/Mac
```bash
dig api.seudominio.com
dig app.seudominio.com
```

### Online
- Use ferramentas como: https://dnschecker.org/
- Digite `api.seudominio.com` e `app.seudominio.com`
- Verifique se ambos apontam para o IP correto

## ⏱️ Tempo de Propagação

- **TTL 14400**: Geralmente leva de 1 a 4 horas para propagar
- **TTL 3600**: Geralmente leva de 30 minutos a 1 hora
- **TTL 300**: Geralmente leva de 5 a 15 minutos

## 🔐 HTTPS (SSL/TLS)

Após configurar os DNS, configure certificados SSL:

### Let's Encrypt (Certbot)
```bash
certbot --apache -d api.seudominio.com
certbot --apache -d app.seudominio.com
```

### Cloudflare
Se usar Cloudflare, ative o SSL/TLS no painel e configure:
- SSL/TLS encryption mode: **Full** ou **Full (strict)**
- Automatic HTTPS Rewrites: **On**

## 📝 Resumo dos Passos

1. ✅ Adicionar registro DNS tipo `A` para `api` apontando para o IP do servidor
2. ✅ Adicionar registro DNS tipo `A` para `app` apontando para o IP do servidor
3. ✅ Configurar VirtualHost no Apache/Nginx
4. ✅ Aguardar propagação DNS (1-4 horas)
5. ✅ Testar acesso aos subdomínios
6. ✅ Configurar SSL/HTTPS (recomendado)

## 🆘 Troubleshooting

### DNS não está resolvendo
- Verifique se o registro foi criado corretamente
- Aguarde a propagação (pode levar até 24 horas)
- Limpe o cache DNS local: `ipconfig /flushdns` (Windows)

### Erro 404 no subdomínio
- Verifique se o VirtualHost está configurado corretamente
- Verifique se o DocumentRoot aponta para `/public`
- Reinicie o Apache/Nginx

### Erro de conexão
- Verifique se o firewall permite conexões na porta 80/443
- Verifique se o servidor está rodando
- Verifique se o IP está correto nos registros DNS

