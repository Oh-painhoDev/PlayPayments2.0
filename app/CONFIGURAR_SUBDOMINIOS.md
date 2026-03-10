# 📋 Guia Completo: Como Criar Subdomínios api.playpayments.com e app.playpayments.com

## ⚠️ IMPORTANTE
Os arquivos de rotas já estão configurados! Você só precisa criar os registros DNS.

---

## 🔍 Passo 1: Descobrir o IP do Seu Servidor

### Se já está em produção:
- Use o IP do servidor onde sua aplicação Laravel está hospedada

### Se está em desenvolvimento local:
- Para testar localmente, use `127.0.0.1` no arquivo `hosts` (veja mais abaixo)

### Como descobrir o IP atual:
```bash
# No servidor Linux/Mac
curl ifconfig.me

# Ou acesse:
https://www.whatismyip.com/
```

---

## 🌐 Passo 2: Configurar DNS no Provedor

### Opção A: Registro.br (se o domínio está lá)

1. Acesse: https://registro.br/
2. Faça login
3. Vá em **Meus Domínios** → **playpayments.com** → **DNS**
4. Adicione os registros:

```
Host: api
Tipo: A
Valor: [SEU_IP_AQUI]
TTL: 3600

Host: app
Tipo: A
Valor: [SEU_IP_AQUI]
TTL: 3600
```

### Opção B: Cloudflare

1. Acesse: https://dash.cloudflare.com/
2. Selecione o domínio **playpayments.com**
3. Vá em **DNS** → **Records**
4. Clique em **Add record**:

**Registro 1:**
```
Type: A
Name: api
IPv4 address: [SEU_IP_AQUI]
Proxy status: DNS only (não ative o proxy se precisar do IP real)
TTL: Auto
```

**Registro 2:**
```
Type: A
Name: app
IPv4 address: [SEU_IP_AQUI]
Proxy status: DNS only
TTL: Auto
```

### Opção C: Hostinger / Outros Provedores

1. Acesse o painel de controle do provedor
2. Vá em **Domínios** → **Gerenciar DNS** ou **DNS Zone**
3. Adicione dois registros A:

```
Tipo: A
Nome/Host: api
Valor/Points to: [SEU_IP_AQUI]
TTL: 3600

Tipo: A
Nome/Host: app
Valor/Points to: [SEU_IP_AQUI]
TTL: 3600
```

---

## 🧪 Passo 3: Testar Localmente (Desenvolvimento)

### Windows:
1. Abra o Bloco de Notas **como Administrador**
2. Abra o arquivo: `C:\Windows\System32\drivers\etc\hosts`
3. Adicione no final:
```
127.0.0.1    api.playpayments.com
127.0.0.1    app.playpayments.com
```
4. Salve o arquivo

### Linux/Mac:
```bash
sudo nano /etc/hosts
```

Adicione:
```
127.0.0.1    api.playpayments.com
127.0.0.1    app.playpayments.com
```

Salve (Ctrl+X, Y, Enter no nano)

### Depois, inicie o Laravel:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Acesse:
- http://api.playpayments.com:8000/health
- http://app.playpayments.com:8000/

---

## ✅ Passo 4: Verificar se Funcionou

### Verificação Rápida (Após 5-30 minutos):
```bash
# Windows CMD
nslookup api.playpayments.com
nslookup app.playpayments.com

# Ou PowerShell
Resolve-DnsName api.playpayments.com
Resolve-DnsName app.playpayments.com
```

### Verificação Online:
- https://dnschecker.org/
  - Digite: `api.playpayments.com` e `app.playpayments.com`
  - Verifique se aparece seu IP em vários servidores DNS

### Testar no Navegador:

**API Subdomain:**
```
https://api.playpayments.online/health
```
Deve retornar:
```json
{
  "status": "ok",
  "service": "API Subdomain - api.playpayments.com",
  "timestamp": "2025-..."
}
```

**APP Subdomain:**
```
https://app.playpayments.com/
```
Deve redirecionar para `/acessar` ou mostrar a página de login.

---

## ⏱️ Tempo de Propagação

- **TTL Baixo (300-3600)**: 5 minutos a 1 hora
- **TTL Médio (3600-14400)**: 1 a 4 horas  
- **TTL Alto (14400+)**: Até 24 horas

**Dica:** Após criar os registros, aguarde alguns minutos e teste. Se não funcionar, aguarde mais tempo.

---

## 🔒 Passo 5: Configurar SSL (HTTPS)

### Com Let's Encrypt (Certbot):
```bash
# Instalar certbot
sudo apt-get install certbot python3-certbot-nginx
# ou
sudo apt-get install certbot python3-certbot-apache

# Gerar certificado para ambos subdomínios
sudo certbot --nginx -d playpayments.com -d api.playpayments.com -d app.playpayments.com
# ou
sudo certbot --apache -d playpayments.com -d api.playpayments.com -d app.playpayments.com
```

### Com Cloudflare:
Se usar Cloudflare com proxy ativado, o SSL é automático.

---

## 🐛 Problemas Comuns

### 1. DNS não resolve
- **Solução:** Aguarde mais tempo (propagação DNS)
- **Solução:** Verifique se criou os registros A corretamente
- **Solução:** Use `nslookup` para verificar

### 2. Erro 404 ou "Site não encontrado"
- **Solução:** Verifique se o servidor web (Apache/Nginx) está configurado para aceitar os subdomínios
- **Solução:** Verifique se os arquivos de rotas foram carregados corretamente

### 3. Funciona no domínio principal mas não nos subdomínios
- **Solução:** O servidor web precisa estar configurado para aceitar requisições nos subdomínios
- **Solução:** Verifique os Virtual Hosts (Apache) ou Server Blocks (Nginx)

### 4. Certificado SSL não funciona para subdomínios
- **Solução:** Gere certificado para todos os domínios: `certbot -d playpayments.com -d api.playpayments.com -d app.playpayments.com`
- **Solução:** Use wildcard: `certbot -d *.playpayments.com -d playpayments.com`

---

## 📝 Resumo Rápido

1. ✅ **Rotas configuradas** - Já está feito!
2. 📝 **Criar registros DNS A** para `api` e `app`
3. ⏱️ **Aguardar propagação** (5 min - 24h)
4. ✅ **Testar** com `nslookup` ou navegador
5. 🔒 **Configurar SSL** (opcional mas recomendado)

---

## 🎯 Resultado Final

Após configurar, você terá:
- `api.playpayments.com` → Todas as rotas de API
- `app.playpayments.com` → Todas as rotas web (dashboard, login, etc.)
- `playpayments.com` → Continua funcionando normalmente





