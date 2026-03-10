# Globo 3D - Vendas por Localização

Uma implementação completa de um globo 3D interativo para visualizar vendas ao redor do mundo.

## 📁 Estrutura do Projeto

```
globo-3d/
├── index.html           # HTML principal
├── css/
│   └── style.css       # Estilos CSS
├── js/
│   └── app.js          # Lógica da aplicação
├── lib/
│   ├── three.min.js    # Three.js (biblioteca 3D)
│   └── globe.gl.min.js # Globe.gl (renderização do globo)
├── img/
│   ├── earth-dark.jpg      # Textura do globo
│   ├── earth-topology.png  # Relevo do globo
│   └── night-sky.png       # Fundo estrelado
└── README.md           # Este arquivo
```

## 🚀 Instalação

### 1. Estrutura criada ✅

Os arquivos HTML, CSS e JS já estão prontos.

### 2. Baixar bibliotecas

Você precisa baixar as bibliotecas e colocá-las na pasta `lib/`:

#### Opção A: Usando URLs diretas

```bash
# Acesse os links abaixo e salve os arquivos em lib/

# Three.js
https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js
-> Salve como: lib/three.min.js

# Globe.gl
https://cdn.jsdelivr.net/npm/globe.gl@2.42.0/dist/globe.gl.min.js
-> Salve como: lib/globe.gl.min.js
```

#### Opção B: Via npm (se tiver Node.js instalado)

```bash
cd globo-3d
npm init -y
npm install three globe.gl
```

Depois copie os arquivos:
```bash
copy node_modules\three\build\three.min.js lib\
copy node_modules\globe.gl\dist\globe.gl.min.js lib\
```

### 3. Baixar imagens do globo

As imagens precisam ser salvas em `img/`:

```
https://cdn.jsdelivr.net/npm/three-globe/example/img/earth-dark.jpg
-> Salve como: img/earth-dark.jpg

https://cdn.jsdelivr.net/npm/three-globe/example/img/earth-topology.png
-> Salve como: img/earth-topology.png

https://cdn.jsdelivr.net/npm/three-globe/example/img/night-sky.png
-> Salve como: img/night-sky.png
```

## 📖 Uso

### Local

1. Abra o arquivo `index.html` em um navegador web moderno
2. O globo deve aparecer com a rotação automática

### Via servidor web

```bash
# Abra um terminal na pasta globo-3d e execute:

# Python 3
python -m http.server 8000

# Node.js (serve)
npx serve

# PHP 8.3 (seu Laragon)
php -S localhost:8000
```

Acesse: `http://localhost:8000`

## ✨ Funcionalidades

- ✅ Globo 3D interativo com rotação automática
- ✅ 18 localizações de vendas mundiais
- ✅ Sistema de cores por intensidade (Verde → Vermelho)
- ✅ Informações ao passar o mouse
- ✅ Estatísticas em tempo real
- ✅ Design responsivo (desktop e mobile)
- ✅ Clique para pausar/retomar rotação
- ✅ Totalmente independente (sem CDNs)

## 🎨 Personalização

### Adicionar mais localizações

Edite `js/app.js` e adicione mais objetos ao array `salesData`:

```javascript
const salesData = [
    { lat: -23.5505, lng: -46.6333, value: 85, country: "Brasil", sales: 450000 },
    // Adicione aqui...
    { lat: YOUR_LAT, lng: YOUR_LNG, value: YOUR_VALUE, country: "SEU_PAIS", sales: YOUR_SALES },
];
```

### Mudar cores

Em `js/app.js`, procure a função `pointColor` e ajuste os valores de cor:

```javascript
.pointColor(d => {
    const intensity = d.value;
    if (intensity >= 85) return '#ef4444';  // Mude esta cor
    if (intensity >= 75) return '#f97316';
    if (intensity >= 60) return '#fbbf24';
    return '#10b981';
})
```

### Mudar velocidade de rotação

Em `js/app.js`, ajuste:

```javascript
globe.controls().autoRotateSpeed = 2;  // Aumente ou diminua este valor
```

## 🌐 Integração com Laravel/PHP

Para usar em sua aplicação Laravel:

1. Copie a pasta `globo-3d` para `public/`
2. Acesse via: `http://seu-dominio/globo-3d/`
3. Ou integre na view existente:

```php
<!-- Em sua view Blade -->
<iframe src="/globo-3d/" style="width: 100%; height: 600px; border: none; border-radius: 12px;"></iframe>
```

## 🔧 Troubleshooting

### "Erro ao inicializar o globo"

- Verifique se as bibliotecas estão em `lib/`
- Abra o Console (F12) para ver o erro exato
- Certifique-se que os caminhos das imagens estão corretos

### Globo não aparece

- Verifique se `three.min.js` e `globe.gl.min.js` foram baixados corretamente
- Tente abrir via servidor HTTP (não funciona bem com `file://`)
- Verifique as imagens em `img/`

### Lentidão/Baixo desempenho

- Reduza a quantidade de pontos em `salesData`
- Diminua o valor de `pointRadius`
- Feche outras abas do navegador

## 📝 Licenças

- **Three.js**: MIT License
- **Globe.gl**: Apache 2.0 License

## 👨‍💻 Suporte

Para dúvidas ou problemas, verifique:
- [Documentação Three.js](https://threejs.org/docs/)
- [Documentação Globe.gl](https://github.com/vasturiano/globe.gl)

---

**Criado em:** 7 de março de 2026
