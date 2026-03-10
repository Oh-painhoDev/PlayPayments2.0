#!/bin/bash
# Script para baixar as dependências do Globo 3D Standalone (Linux/macOS)

clear
echo ""
echo "===================================================="
echo "      GLOBO 3D STANDALONE - DOWNLOAD AUTO"
echo "===================================================="
echo ""

cd "$(dirname "$0")"

mkdir -p lib
mkdir -p img

echo "[1/5] Criando pastas necessárias..."
echo "OK - Pastas criadas"
echo ""

echo "[2/5] Baixando Three.js..."
curl -s -o lib/three.min.js https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js && echo "OK - Three.js baixado" || echo "ERRO ao baixar Three.js"

echo "[3/5] Baixando Globe.gl..."
curl -s -o lib/globe.gl.min.js https://cdn.jsdelivr.net/npm/globe.gl@2.42.0/dist/globe.gl.min.js && echo "OK - Globe.gl baixado" || echo "ERRO ao baixar Globe.gl"

echo "[4/5] Baixando texturas do globo..."
curl -s -o img/earth-dark.jpg https://cdn.jsdelivr.net/npm/three-globe/example/img/earth-dark.jpg && echo "OK - Textura earth-dark baixada" || echo "ERRO"
curl -s -o img/earth-topology.png https://cdn.jsdelivr.net/npm/three-globe/example/img/earth-topology.png && echo "OK - Textura earth-topology baixada" || echo "ERRO"
curl -s -o img/night-sky.png https://cdn.jsdelivr.net/npm/three-globe/example/img/night-sky.png && echo "OK - Textura night-sky baixada" || echo "ERRO"

echo "[5/5] Finalizando..."
echo ""
echo "===================================================="
echo "      DOWNLOAD CONCLUÍDO COM SUCESSO!"
echo "===================================================="
echo ""
echo "Arquivos criados em:"
echo "   - lib/three.min.js"
echo "   - lib/globe.gl.min.js"
echo "   - img/earth-dark.jpg"
echo "   - img/earth-topology.png"
echo "   - img/night-sky.png"
echo ""
echo "Para iniciar o servidor:"
echo "   - Python: python3 -m http.server 8000"
echo "   - Node.js: npx serve"
echo ""
echo "Depois acesse: http://localhost:8000"
echo ""
