# Guia Rápido - Globo 3D Independente

## ✅ O que foi criado

Uma estrutura completa de pastas **totalmente independente** que funciona em qualquer lugar!

```
globo-3d/
├── index.html                          # Página principal
├── css/style.css                       # Estilos
├── js/app.js                           # Lógica
├── lib/                                # (vazio - download abaixo)
├── img/                                # (vazio - download abaixo)
├── baixar-dependencias.bat             # ⬇️ Execute isso (Windows)
├── baixar-dependencias.sh              # ⬇️ Execute isso (Mac/Linux)
└── README.md                           # Documentação completa
```

## 🚀 Como usar (3 passos)

### 1️⃣ Abra o Terminal

**Windows:**
- Vá para a pasta `globo-3d`
- Clique em `baixar-dependencias.bat`

**Mac/Linux:**
```bash
cd globo-3d
chmod +x baixar-dependencias.sh
./baixar-dependencias.sh
```

### 2️⃣ Aguarde o download

As bibliotecas e imagens serão baixadas automaticamente.

### 3️⃣ Abra no navegador

Duas opções:

**Opção A: Direto (via próprio servidor)**
```bash
# Vá para a pasta globo-3d no terminal e execute:

# Windows
php -S localhost:8000

# Mac/Linux
python3 -m http.server 8000
```

Acesse: `http://localhost:8000`

**Opção B: Abrir arquivo direto**
- Clique duplo em `index.html`

## 📦 O que você pode fazer agora

- ✅ Copiar a pasta `globo-3d` para qualquer lugar
- ✅ Usar em um servidor Apache/Nginx
- ✅ Integrar no Laravel
- ✅ Colocar em um subdomain
- ✅ Colocar em um CDN
- ✅ Vender como template

## 🔄 Se algo der errado

Se o script de download não funcionar, faça manualmente:

1. Crie as pastas vazias `lib/` e `img/`
2. Baixe esses arquivos manualmente:

```
lib/three.min.js (from CDN):
https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js

lib/globe.gl.min.js (from CDN):
https://cdn.jsdelivr.net/npm/globe.gl@2.42.0/dist/globe.gl.min.js

img/earth-dark.jpg:
https://cdn.jsdelivr.net/npm/three-globe/example/img/earth-dark.jpg

img/earth-topology.png:
https://cdn.jsdelivr.net/npm/three-globe/example/img/earth-topology.png

img/night-sky.png:
https://cdn.jsdelivr.net/npm/three-globe/example/img/night-sky.png
```

3. Coloque-os nas respectivas pastas

## 💡 Dicas

- Você pode renomear a pasta `globo-3d` para qualquer coisa
- Funciona offline após baixar as dependências
- Pode ser instalado num subdomínio (ex: globo.seusite.com)
- Compatível com todos os navegadores modernos

---

**Precisa de mais ajuda?** Leia o README.md completo!
