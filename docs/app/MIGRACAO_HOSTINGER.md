# 🚀 Guia Completo de Migração para VPS Hostinger

## 📋 Pré-requisitos

1. Acesso SSH à VPS Hostinger
2. Credenciais do banco de dados MySQL
3. Domínio configurado (ex: api.playpayments.com, app.playpayments.com)

---

## 🔧 PASSO 1: Preparar o Ambiente na VPS

### 1.1 Conectar via SSH
```bash
ssh usuario@seu-ip-vps
# ou
ssh usuario@seu-dominio.com
```

### 1.2 Atualizar o sistema
```bash
sudo apt update
sudo apt upgrade -y
```

### 1.3 Instalar dependências
```bash
# PHP 8.2 e extensões necessárias
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-intl php8.2-opcache

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Nginx
sudo apt install -y nginx

# MySQL (se não estiver instalado)
sudo apt install -y mysql-server mysql-client

# Git
sudo apt install -y git
```

### 1.4 Verificar versões
```bash
php -v
composer --version
nginx -v
mysql --version
```

---

## 📦 PASSO 2: Configurar o Projeto

### 2.1 Criar diretório do projeto
```bash
# Se usar subdomínios separados
sudo mkdir -p /var/www/api.playpayments.com
sudo mkdir -p /var/www/app.playpayments.com

# OU se usar um único domínio
sudo mkdir -p /var/www/playpayments.com
cd /var/www/playpayments.com
```

### 2.2 Clonar ou fazer upload do projeto
```bash
# Opção 1: Via Git
cd /var/www/playpayments.com
sudo git clone https://seu-repositorio.git .

# Opção 2: Via SCP (do seu computador local)
# scp -r /caminho/local/projeto/* usuario@vps:/var/www/playpayments.com/

# Opção 3: Via FileZilla ou gerenciador de arquivos da Hostinger
```

### 2.3 Definir permissões
```bash
cd /var/www/playpayments.com
sudo chown -R www-data:www-data /var/www/playpayments.com
sudo chmod -R 755 /var/www/playpayments.com
sudo chmod -R 775 storage bootstrap/cache
```

---

## 🗄️ PASSO 3: Configurar Banco de Dados

### 3.1 Criar banco de dados e usuário
```bash
sudo mysql -u root -p
```

No MySQL:
```sql
CREATE DATABASE playpayments CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'playpayments_user'@'localhost' IDENTIFIED BY 'senha_forte_aqui';
GRANT ALL PRIVILEGES ON playpayments.* TO 'playpayments_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3.2 Importar dump (se tiver)
```bash
mysql -u playpayments_user -p playpayments < backup.sql
```

---

## ⚙️ PASSO 4: Configurar Laravel

### 4.1 Instalar dependências
```bash
cd /var/www/playpayments.com
composer install --no-dev --optimize-autoloader
```

### 4.2 Configurar arquivo .env
```bash
cp .env.example .env
nano .env
```

**Configuração do .env:**
```env
APP_NAME=playpayments
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://app.playpayments.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=playpayments
DB_USERNAME=playpayments_user
DB_PASSWORD=sua_senha_aqui

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 4.3 Gerar APP_KEY
```bash
php artisan key:generate
```

### 4.4 Executar migrations
```bash
php artisan migrate --force
```

### 4.5 Criar link simbólico do storage
```bash
php artisan storage:link
```

### 4.6 Limpar e otimizar cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Otimizar para produção
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## 🌐 PASSO 5: Configurar Nginx

### 5.1 Configuração para API (api.playpayments.com)
```bash
sudo nano /etc/nginx/sites-available/api.playpayments.com
```

**Conteúdo:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name api.playpayments.com;
    
    # Redirecionar HTTP para HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name api.playpayments.com;
    
    root /var/www/playpayments.com/public;
    index index.php;
    
    # SSL (configurar com Let's Encrypt depois)
    # ssl_certificate /etc/letsencrypt/live/api.playpayments.com/fullchain.pem;
    # ssl_certificate_key /etc/letsencrypt/live/api.playpayments.com/privkey.pem;
    
    # Logs
    access_log /var/log/nginx/api.playpayments.com.access.log;
    error_log /var/log/nginx/api.playpayments.com.error.log;
    
    # Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    # Headers de segurança
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
```

### 5.2 Configuração para App (app.playpayments.com)
```bash
sudo nano /etc/nginx/sites-available/app.playpayments.com
```

**Conteúdo:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name app.playpayments.com;
    
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name app.playpayments.com;
    
    root /var/www/playpayments.com/public;
    index index.php;
    
    # SSL
    # ssl_certificate /etc/letsencrypt/live/app.playpayments.com/fullchain.pem;
    # ssl_certificate_key /etc/letsencrypt/live/app.playpayments.com/privkey.pem;
    
    access_log /var/log/nginx/app.playpayments.com.access.log;
    error_log /var/log/nginx/app.playpayments.com.error.log;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
```

### 5.3 Ativar sites
```bash
sudo ln -s /etc/nginx/sites-available/api.playpayments.com /etc/nginx/sites-enabled/
sudo ln -s /etc/nginx/sites-available/app.playpayments.com /etc/nginx/sites-enabled/

# Testar configuração
sudo nginx -t

# Reiniciar Nginx
sudo systemctl restart nginx
```

---

## 🔒 PASSO 6: Configurar SSL (Let's Encrypt)

### 6.1 Instalar Certbot
```bash
sudo apt install -y certbot python3-certbot-nginx
```

### 6.2 Obter certificados SSL
```bash
# Para API
sudo certbot --nginx -d api.playpayments.com

# Para App
sudo certbot --nginx -d app.playpayments.com

# Renovação automática
sudo certbot renew --dry-run
```

---

## 🔄 PASSO 7: Configurar DNS

### 7.1 No painel do domínio, adicionar registros:

**Para api.playpayments.com:**
```
Tipo: A
Nome: api
Valor: IP_DA_VPS
TTL: 3600
```

**Para app.playpayments.com:**
```
Tipo: A
Nome: app
Valor: IP_DA_VPS
TTL: 3600
```

**OU usar CNAME:**
```
Tipo: CNAME
Nome: api
Valor: playpayments.com
TTL: 3600

Tipo: CNAME
Nome: app
Valor: playpayments.com
TTL: 3600
```

---

## 🗄️ PASSO 8: Executar Migrations Específicas

### 8.1 Verificar migrations pendentes
```bash
php artisan migrate:status
```

### 8.2 Executar todas as migrations
```bash
php artisan migrate --force
```

### 8.3 Migrations importantes (se necessário executar individualmente)
```bash
# Criar tabela baas_credentials
php artisan migrate --path=database/migrations/2025_12_18_060000_create_baas_credentials_table.php --force

# Adicionar withdrawal_fee (se a tabela já existir)
php artisan migrate --path=database/migrations/2025_12_18_055900_add_withdrawal_fee_to_baas_credentials_table.php --force
```

---

## 🧹 PASSO 9: Limpar e Otimizar

### 9.1 Limpar todos os caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### 9.2 Otimizar para produção
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
composer dump-autoload --optimize
```

---

## 🔧 PASSO 10: Configurar Permissões Finais

```bash
cd /var/www/playpayments.com

# Propriedade
sudo chown -R www-data:www-data .

# Permissões de diretórios
sudo find . -type d -exec chmod 755 {} \;

# Permissões de arquivos
sudo find . -type f -exec chmod 644 {} \;

# Permissões especiais para storage e cache
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

---

## 🚀 PASSO 11: Configurar Supervisor (para filas, se necessário)

### 11.1 Instalar Supervisor
```bash
sudo apt install -y supervisor
```

### 11.2 Configurar worker de fila
```bash
sudo nano /etc/supervisor/conf.d/playpayments-worker.conf
```

**Conteúdo:**
```ini
[program:playpayments-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/playpayments.com/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/playpayments.com/storage/logs/worker.log
stopwaitsecs=3600
```

### 11.3 Ativar Supervisor
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start playpayments-worker:*
```

---

## 🔥 PASSO 12: Configurar Firewall

```bash
# Instalar UFW
sudo apt install -y ufw

# Permitir SSH
sudo ufw allow 22/tcp

# Permitir HTTP e HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Ativar firewall
sudo ufw enable

# Verificar status
sudo ufw status
```

---

## ✅ PASSO 13: Verificações Finais

### 13.1 Verificar serviços
```bash
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
```

### 13.2 Testar aplicação
```bash
# Testar rota de health
curl https://api.playpayments.online/up

# Testar conexão com banco
php artisan tinker
>>> DB::connection()->getPdo();
```

### 13.3 Verificar logs
```bash
# Logs do Laravel
tail -f storage/logs/laravel.log

# Logs do Nginx
sudo tail -f /var/log/nginx/error.log
```

---

## 📝 COMANDOS ÚTEIS PARA MANUTENÇÃO

### Limpar caches
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Verificar rotas
```bash
php artisan route:list
```

### Verificar migrations
```bash
php artisan migrate:status
```

### Reiniciar serviços
```bash
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart mysql
```

### Verificar espaço em disco
```bash
df -h
du -sh /var/www/playpayments.com/*
```

---

## 🆘 TROUBLESHOOTING

### Erro 500
```bash
# Verificar logs
tail -f storage/logs/laravel.log
sudo tail -f /var/log/nginx/error.log

# Verificar permissões
ls -la storage bootstrap/cache

# Limpar cache
php artisan optimize:clear
```

### Erro de conexão com banco
```bash
# Testar conexão
php artisan tinker
>>> DB::connection()->getPdo();

# Verificar .env
cat .env | grep DB_
```

### Erro de permissão
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 📌 NOTAS IMPORTANTES

1. **Nunca commite o arquivo `.env`**
2. **Sempre use `APP_DEBUG=false` em produção**
3. **Configure backups automáticos do banco de dados**
4. **Monitore os logs regularmente**
5. **Mantenha o sistema atualizado**

---

## 🔗 Links Úteis

- **Webhook PluggouCash:** `https://seu-dominio.com/webhook/pluggou`
- **Health Check:** `https://api.playpayments.online/up`
- **Painel Admin:** `https://app.playpayments.com/admin`

---

**✅ Migração concluída!**

