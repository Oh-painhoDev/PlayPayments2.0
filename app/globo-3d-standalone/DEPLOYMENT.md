# 🚀 Guia de Deploy - Globo 3D Standalone

Instruções passo-a-passo para colocar seu globo em produção.

---

## 1️⃣ Preparação Local

### Pré-requisitos
- Folder `globo-3d-standalone` completo
- Dependências baixadas (`lib/` e `img/`)
- Conexão FTP ou SSH com seu servidor

### Verificar antes de fazer upload

```bash
cd globo-3d-standalone

# Windows
dir lib\
dir img\

# Mac/Linux
ls -la lib/
ls -la img/
```

Deve ter:
- `lib/three.min.js` (~2MB)
- `lib/globe.gl.min.js` (~500KB)
- `img/earth-dark.jpg`
- `img/earth-topology.png`
- `img/night-sky.png`

---

## 2️⃣ Deploy em Hosting Popular

### Hostinger / Bluehost / etc

**Via FTP (Recomendado):**
1. Abra seu cliente FTP (FileZilla recomendado)
2. Conecte com as credenciais fornecidas
3. Navegue para `public_html/`
4. Faça upload de **toda** a pasta `globo-3d-standalone`
5. Resultado:
```
public_html/
└── globo-3d-standalone/
    ├── index.html
    ├── css/
    ├── js/
    ├── lib/
    └── img/
```

**Acesso:**
```
https://seu-dominio.com/globo-3d-standalone/
```

---

### Criar Subdomínio (Recomendado)

**No painel cPanel:**
1. Vá para **Add-on Domains** ou **Subdomains**
2. Crie: `globo.seu-dominio.com`
3. Apontando para: `/public_html/globo-3d-standalone/`
4. Salve

**Acesso:**
```
https://globo.seu-dominio.com/
```

---

## 3️⃣ Deploy em VPS / Servidor Próprio

### Via SSH (Linux/Mac)

```bash
# 1. Conecte ao servidor
ssh user@seu-servidor.com

# 2. Navegue para web root
cd /home/user/public_html/

# 3. Copie via SCP (do seu PC)
scp -r /caminho/local/globo-3d-standalone/ user@seu-servidor.com:/home/user/public_html/

# 4. Verifique permissões
chmod 755 globo-3d-standalone/
chmod 644 globo-3d-standalone/index.html
chmod 644 globo-3d-standalone/css/*
chmod 644 globo-3d-standalone/js/*

# 5. Teste no navegador
# https://seu-servidor.com/globo-3d-standalone/
```

### Configurar HTTPS

**Com Let's Encrypt (Gratuito):**
```bash
sudo certbot certonly --webroot -w /home/user/public_html -d globo.seu-dominio.com
```

**No Nginx:**
```nginx
server {
    listen 443 ssl;
    server_name globo.seu-dominio.com;

    ssl_certificate /etc/letsencrypt/live/globo.seu-dominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/globo.seu-dominio.com/privkey.pem;

    root /home/user/public_html/globo-3d-standalone/;
    
    location / {
        try_files $uri /index.html;
    }
}
```

---

## 4️⃣ Deploy com Docker

### Dockerfile
```dockerfile
FROM nginx:alpine

# Copiar arquivos
COPY globo-3d-standalone/ /usr/share/nginx/html/

# Configurar nginx
RUN echo 'server { \
    listen 80; \
    root /usr/share/nginx/html; \
    index index.html; \
    location / { \
        try_files $uri /index.html; \
    } \
}' > /etc/nginx/conf.d/default.conf

EXPOSE 80
```

### Build e Run
```bash
# Build
docker build -t globo-3d .

# Run
docker run -p 8080:80 globo-3d

# Acesse: http://localhost:8080
```

### Docker Compose
```yaml
version: '3.8'
services:
  globo:
    image: nginx:alpine
    ports:
      - "8080:80"
    volumes:
      - ./globo-3d-standalone:/usr/share/nginx/html
```

```bash
docker-compose up -d
```

---

## 5️⃣ Deploy com GitHub Pages (Gratuito!)

### Setup
```bash
cd globo-3d-standalone

# 1. Inicialize git
git init

# 2. Crie .gitignore
echo "node_modules/" > .gitignore

# 3. Commit
git add .
git commit -m "Globo 3D"

# 4. Crie repo no GitHub
# https://github.com/new
```

### Deploy
```bash
# Push para branch gh-pages
git push origin main:gh-pages

# Ative Pages nas settings do repo
# Seu site estará em: https://seu-username.github.io/globo-3d-standalone/
```

---

## 6️⃣ Verificação Pós-Deploy

Após fazer upload, verifique:

- ✅ Globo 3D carrega?
- ✅ Globo e rotação?
- ✅ Cores dos pontos aparecem?
- ✅ Estatísticas carregam?
- ✅ Console sem erros? (F12)
- ✅ Funciona em mobile?
- ✅ Performance boa (<2s load)?

### Checklist de Debug

```javascript
// Abra o Console (F12)

// Verifique bibliotecas
console.log(THREE);      // Deve aparecer
console.log(Globe);      // Deve aparecer

// Verifique arquivos
// F12 → Network → confira lib/ e img/
```

---

## 7️⃣ Otimizations para Produção

### Comprime arquivos
```bash
gzip -k index.html
gzip -k css/style.css
gzip -k js/app.js
```

### Cache Headers (Apache .htaccess)
```apache
<FilesMatch "\.(jpg|jpeg|png|gif|js|css)$">
  Header set Cache-Control "max-age=31536000, public"
</FilesMatch>

<FilesMatch "\.html$">
  Header set Cache-Control "max-age=3600, public"
</FilesMatch>
```

### Nginx
```nginx
location ~* \.(jpg|jpeg|png|gif|js|css)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}

location ~* \.html$ {
    expires 1h;
}
```

---

## 8️⃣ Troubleshooting após Deploy

### 404 - Página não encontrada
```
publicados em: /globo-3d-standalone/
Não: /globo-3d-standalone (sem barra)
```

### CORS Error
Se integrar com API externa, configure CORS:
```
Header set Access-Control-Allow-Origin "*"
```

### Certificado SSL não confiável
Use Let's Encrypt:
```bash
certbot certonly --webroot -d globo.seu-dominio.com
```

### Globo não renderiza
- Verifique `lib/` e `img/` foram uploadados
- Teste o Console (F12)
- Verifique erros de rede

---

## 📊 Performance

**Benchmarks:**

| Métrica | Valor | Alvo |
|---------|-------|------|
| Load Time | <2s | ✅ |
| FPS | 60 | ✅ |
| Size | 3MB | ✅ |
| Mobile | Responsivo | ✅ |

---

## 🔒 Segurança

- ✅ Sem banco de dados
- ✅ Sem uploads de usuário
- ✅ Sem tokens sensíveis
- ✅ HTTPS recomendado
- ✅ Content Security Policy:

```
Content-Security-Policy: 
  default-src 'self'; 
  script-src 'self'
```

---

## 📞 Precisa de Ajuda?

1. Verifique Console (F12)
2. Leia README.md
3. Verifique Network tab (F12 → Network)
4. Teste localmente primeiro

---

**Pronto! Seu globo está no ar! 🌍**
