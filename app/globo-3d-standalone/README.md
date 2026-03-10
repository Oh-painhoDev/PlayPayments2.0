# 🌍 Globo 3D Standalone

**Versão 100% independente e pronta para usar em qualquer lugar!**

---

## 📦 O que você recebeu

Uma pasta **totalmente autossuficiente** `globo-3d-standalone` que funciona:
- ✅ Em qualquer servidor web (Apache, Nginx, IIS)
- ✅ Em qualquer máquina (Windows, Mac, Linux)
- ✅ Sem dependências do projeto principal
- ✅ Pode ser movida/copiada livremente
- ✅ Pode ser integrada em qualquer lugar

---

## 🚀 Início Rápido (3 passos)

### 1️⃣ Abra o Terminal

**Windows (PowerShell):**
```powershell
cd "C:\caminho\para\globo-3d-standalone"
.\download-deps.bat
```

**Mac/Linux (Terminal):**
```bash
cd /caminho/para/globo-3d-standalone
chmod +x download-deps.sh
./download-deps.sh
```

### 2️⃣ Aguarde o download

Os scripts vão baixar automaticamente:
- `lib/three.min.js` (2MB)
- `lib/globe.gl.min.js` (500KB)
- `img/earth-dark.jpg`, `img/earth-topology.png`, `img/night-sky.png`

### 3️⃣ Inicie o servidor

**Com PHP (seu Laragon):**
```bash
php -S localhost:8000
```

**Com Python:**
```bash
python -m http.server 8000
```

**Com Node.js:**
```bash
npx serve
```

Acesse: **http://localhost:8000**

---

## 📂 Estrutura de Arquivos

```
globo-3d-standalone/
├── index.html                 # Página principal
├── css/
│   └── style.css             # Estilos
├── js/
│   └── app.js                # Lógica
├── lib/                      # (download automático)
│   ├── three.min.js
│   └── globe.gl.min.js
├── img/                      # (download automático)
│   ├── earth-dark.jpg
│   ├── earth-topology.png
│   └── night-sky.png
├── public/                   # Arquivos estáticos
├── download-deps.bat         # Download automático (Windows)
├── download-deps.sh          # Download automático (Mac/Linux)
├── package.json              # Informações do projeto
├── README.md                 # Este arquivo
└── DEPLOYMENT.md             # Guia de deploy
```

---

## 🌐 Onde Usar

### 🏠 Em Casa / Desenvolvimento Local
```bash
php -S localhost:8000
# Abra http://localhost:8000
```

### 🖥️ Servidor Próprio (Apache/Nginx)
1. Faça upload via FTP
2. Aponte o domínio para a pasta
3. Acesse o domínio no navegador

### ☁️ Hosting (Hostinger, Bluehost, etc)
1. Extraia o ZIP na pasta `public_html/:
```
public_html/
└── globo-3d/
    ├── index.html
    ├── css/
    ├── js/
    ├── lib/
    └── img/
```
2. Abra: `https://seu-dominio.com/globo-3d/`

### 🔗 Subdomínio
Crie um subdomínio (ex: `globo.seu-dominio.com`) apontando para a pasta.

### 🐳 Docker (Advanced)
```dockerfile
FROM nginx:latest
COPY globo-3d-standalone /usr/share/nginx/html
EXPOSE 80
```

---

## ⚙️ Customização

### Adicionar mais países/cidades

Edite `js/app.js` e adicione ao array `salesData`:

```javascript
const salesData = [
    // Formato: { lat, lng, value (0-100), country, sales }
    { lat: -23.5505, lng: -46.6333, value: 85, country: "Brasil", sales: 450000 },
    { lat: 0, lng: 0, value: 50, country: "Seu País", sales: 100000 }, // Adicione aqui
];
```

### Mudar cores
Em `js/app.js`, procure por `pointColor`:
```javascript
.pointColor(d => {
    if (d.value >= 85) return '#ef4444';  // Vermelho
    if (d.value >= 75) return '#f97316';  // Laranja
    if (d.value >= 60) return '#fbbf24';  // Amarelo
    return '#10b981';                     // Verde
})
```

### Mudar velocidade
Em `js/app.js`:
```javascript
globe.controls().autoRotateSpeed = 2;  // 1-5: mais baixo = mais lento
```

### Mudar tamanho
Em `css/style.css`:
```css
#globe-container {
    height: 500px;  /* Mude este valor */
}
```

---

## 🔧 Troubleshooting

### Erro: "Globo não aparece"
1. Verifique se está usando um servidor HTTP (não `file://`)
2. Abra o Console (F12) para ver erros
3. Verifique se as bibliotecas foram baixadas em `lib/`

### Erro: "Bibliotecas não encontradas"
```bash
# Re-execute o script de download
.\download-deps.bat          # Windows
./download-deps.sh           # Mac/Linux
```

### Página carrega mas globo vazio
- Verifique se as imagens em `img/` foram baixadas
- Tente limpar o cache do navegador (Ctrl+Shift+Delete)
- Abra o Console para ver mensagens de erro

### Lentidão/Lagado
- Reduza pontos de dados em `salesData`
- Aumente `pointRadius` em `js/app.js` para reduzir

---

## 📤 Deploy em Produção

### 1. Prepare os arquivos

```bash
# Certifique-se que tudo foi baixado
ls -la lib/
ls -la img/
```

### 2. Faça upload para o servidor

**Via FTP:**
```
Faça upload de TODA a pasta globo-3d-standalone
```

**Via SSH/SCP:**
```bash
scp -r globo-3d-standalone/ user@server:/home/user/public_html/
```

### 3. Configure seu domínio

**com subdomínio:**
```
globo.seu-dominio.com → /home/user/public_html/globo-3d-standalone
```

**com pasta:**
```
seu-dominio.com/globo-3d/ → /home/user/public_html/globo-3d-standalone
```

### 4. Teste

Abra no navegador e verifique se o globo aparece.

---

## 📊 Recursos

- **Linguagem:** HTML5, CSS3, JavaScript (ES6+)
- **Biblioteca 3D:** Three.js (MIT License)
- **Globo:** Globe.gl (Apache 2.0 License)
- **Tamanho:** ~3MB (com bibliotecas)
- **Performance:** ~60 FPS em navegadores modernos
- **Compatibilidade:** Chrome, Firefox, Safari, Edge

---

## 🎓 Integração com Laravel

Se quiser integrar com Laravel/Blade:

### Opção 1: Via Iframe
```blade
<iframe 
    src="/globo-3d/" 
    style="width: 100%; height: 600px; border: none; border-radius: 12px;">
</iframe>
```

### Opção 2: Como Component
```php
<!-- Em uma view -->
<x-globo-3d />
```

### Opção 3: API Dinâmica
Adicione um endpoint em `routes/api.php`:
```php
Route::get('/api/vendas-por-pais', function () {
    return Venda::groupBy('pais')->get();
});
```

Edite `js/app.js` para usar a API.

---

## 📝 Licenças

- **Three.js**: MIT License
- **Globe.gl**: Apache 2.0 License
- **Este projeto**: Seu projeto - use livremente!

---

## 🆘 Suporte

- [Documentação Three.js](https://threejs.org/docs/)
- [GitHub Globe.gl](https://github.com/vasturiano/globe.gl)
- [MDN Web Docs](https://developer.mozilla.org/)

---

**Criado em:** 7 de março de 2026  
**Versão:** 1.0.0 Standalone  
**Status:** ✅ Pronto para produção
