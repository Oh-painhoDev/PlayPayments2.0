# Como Verificar se os Subdomínios Estão Configurados

## 1. Verificação via DNS Checker (Online)
Acesse: https://dnschecker.org/

Digite:
- `api.playpayments.com` - deve apontar para seu IP
- `app.playpayments.com` - deve apontar para seu IP

## 2. Verificação via Terminal/CMD

```bash
# Windows CMD
nslookup api.playpayments.com
nslookup app.playpayments.com

# PowerShell
Resolve-DnsName api.playpayments.com
Resolve-DnsName app.playpayments.com
```

## 3. Verificação via Navegador

Após a propagação DNS (pode levar de alguns minutos a 24 horas):
- `http://api.playpayments.com/health` - deve retornar JSON
- `http://app.playpayments.com/` - deve redirecionar para /acessar

## 4. Tempo de Propagação DNS

- **TTL Baixo (300-3600)**: Propagação rápida (5 minutos - 1 hora)
- **TTL Alto (14400+)**: Propagação lenta (até 24 horas)

## 5. Se Estiver em Desenvolvimento Local

Para testar localmente sem DNS, edite o arquivo `hosts`:

**Windows**: `C:\Windows\System32\drivers\etc\hosts`
**Linux/Mac**: `/etc/hosts`

Adicione:
```
127.0.0.1    api.playpayments.com
127.0.0.1    app.playpayments.com
```

Depois acesse:
- `http://api.playpayments.com:8000/health` (se usar Laravel serve)
- `http://app.playpayments.com:8000/` (se usar Laravel serve)

## 6. Configuração do Servidor Web

### Apache (.htaccess ou Virtual Host)
Certifique-se de que o Apache está configurado para aceitar ambos os subdomínios apontando para o mesmo diretório público.

### Nginx
Certifique-se de que o Nginx tem configuração para aceitar ambos os subdomínios apontando para o mesmo diretório.

## Problemas Comuns

1. **DNS não propagou**: Aguarde até 24 horas ou reduza o TTL
2. **Subdomínio não resolve**: Verifique se criou os registros A corretamente
3. **Erro 404**: Verifique se o servidor web está configurado para aceitar os subdomínios
4. **Certificado SSL**: Lembre-se de configurar SSL (Let's Encrypt) para os subdomínios também





