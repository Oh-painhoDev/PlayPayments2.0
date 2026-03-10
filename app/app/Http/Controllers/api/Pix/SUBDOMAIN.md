# Configuração do Subdomínio API

## Estrutura para Subdomínio API

Esta pasta contém a estrutura da API PIX para uso em subdomínio (ex: `api.dominio.com`).

## Estrutura de Arquivos

```
app/Http/Controllers/Api/Pix/
├── TransactionsController.php  # Controller principal da API
├── README.md                    # Documentação da API
├── AUTHENTICATION.md            # Documentação de autenticação
└── SUBDOMAIN.md                 # Este arquivo
```

## Configuração do Servidor Web

### Nginx

Para configurar o subdomínio API no Nginx, adicione o seguinte bloco no seu arquivo de configuração:

```nginx
server {
    listen 80;
    server_name api.dominio.com;
    
    root /caminho/para/seu/projeto/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Apache

Para configurar no Apache, adicione no arquivo `.htaccess` ou configuração do virtual host:

```apache
<VirtualHost *:80>
    ServerName api.dominio.com
    DocumentRoot /caminho/para/seu/projeto/public
    
    <Directory /caminho/para/seu/projeto/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Rotas Disponíveis

Todas as rotas estão disponíveis em:

### Domínio Principal
- `https://dominio.com/api/v1/transactions`

### Subdomínio API (se configurado)
- `https://api.dominio.com/v1/transactions`

## URLs de Exemplo

### Listar Transações
```
GET https://api.dominio.com/v1/transactions
GET https://dominio.com/api/v1/transactions
```

### Buscar Transação
```
GET https://api.dominio.com/v1/transactions/{id}
GET https://dominio.com/api/v1/transactions/{id}
```

### Criar Transação PIX
```
POST https://api.dominio.com/v1/transactions
POST https://dominio.com/api/v1/transactions
```

## Configuração no .env

Para configurar o subdomínio, você pode adicionar no arquivo `.env`:

```env
APP_URL=https://dominio.com
API_SUBDOMAIN=api.dominio.com
```

## Autenticação

A autenticação funciona da mesma forma em ambos os domínios:

### Para Consultar (GET)
- Public Key ou Private Key

### Para Criar PIX (POST)
- **REQUER AMBOS**: Public Key E Private Key

Veja a documentação completa em: [AUTHENTICATION.md](./AUTHENTICATION.md)

## Exemplo de Uso

### Criar PIX via Subdomínio

```bash
curl -X POST "https://api.dominio.com/v1/transactions" \
  -H "X-Public-Key: PB-playpayments-seu-public-key-aqui" \
  -H "X-Private-Key: SK-playpayments-seu-private-key-aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 10.00,
    "payment_method": "pix",
    "customer": {
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900"
    }
  }'
```

## Notas Importantes

1. **CORS**: Se estiver usando subdomínio diferente, configure CORS no Laravel
2. **SSL**: Recomenda-se usar HTTPS em produção
3. **Rate Limiting**: Configure rate limiting para proteger a API
4. **Logs**: Monitore os logs de acesso à API

## Troubleshooting

### Erro 404 no Subdomínio

- Verifique se o DNS está configurado corretamente
- Verifique se o servidor web está configurado para o subdomínio
- Verifique se o arquivo `routes/api-subdomain.php` está sendo carregado

### Erro de Autenticação

- Verifique se está enviando ambos os tokens (Public Key E Private Key) para criar PIX
- Verifique se os tokens estão corretos no painel do sistema
- Verifique se o usuário está ativo e não bloqueado

## Suporte

Para mais informações, consulte:
- [README.md](./README.md) - Documentação completa da API
- [AUTHENTICATION.md](./AUTHENTICATION.md) - Documentação de autenticação

