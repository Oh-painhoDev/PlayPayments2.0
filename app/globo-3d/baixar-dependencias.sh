#!/bin/bash
# Script para baixar as dependências do Globo 3D (Linux/macOS)

echo "===================================="
echo "Globo 3D - Baixar Dependências"
echo "===================================="
echo ""

# Ir para o diretório do script
cd "$(dirname "$0")"

# Criar pastas se não existirem
mkdir -p lib
mkdir -p img

echo "Baixando biblioteca Three.js..."
curl -o lib/three.min.js https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js

echo "Baixando biblioteca Globe.gl..."
curl -o lib/globe.gl.min.js https://cdn.jsdelivr.net/npm/globe.gl@2.42.0/dist/globe.gl.min.js

echo "Baixando texturas do globo..."
curl -o img/earth-dark.jpg https://cdn.jsdelivr.net/npm/three-globe/example/img/earth-dark.jpg
curl -o img/earth-topology.png https://cdn.jsdelivr.net/npm/three-globe/example/img/earth-topology.png
curl -o img/night-sky.png https://cdn.jsdelivr.net/npm/three-globe/example/img/night-sky.png

echo ""
echo "===================================="
echo "Download concluído!"
echo "===================================="
echo ""
echo "Verifique se os arquivos foram criados em:"
echo "- lib/three.min.js"
echo "- lib/globe.gl.min.js"
echo "- img/earth-dark.jpg"
echo "- img/earth-topology.png"
echo "- img/night-sky.png"
echo ""
echo "Agora você pode abrir index.html no navegador."
