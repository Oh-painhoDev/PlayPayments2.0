#!/bin/bash
# Verificar integridade da instalação

echo "================================"
echo "  Verificador do Globo 3D"
echo "================================"
echo ""

# Cores
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

cd "$(dirname "$0")"

# Verificar estrutura
echo "Verificando estrutura..."

files=(
    "index.html"
    "css/style.css"
    "js/app.js"
    "package.json"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓${NC} $file"
    else
        echo -e "${RED}✗${NC} $file FALTANDO"
    fi
done

echo ""
echo "Verificando dependências baixadas..."

deps=(
    "lib/three.min.js"
    "lib/globe.gl.min.js"
    "img/earth-dark.jpg"
    "img/earth-topology.png"
    "img/night-sky.png"
)

for dep in "${deps[@]}"; do
    if [ -f "$dep" ]; then
        size=$(du -h "$dep" | cut -f1)
        echo -e "${GREEN}✓${NC} $dep ($size)"
    else
        echo -e "${RED}✗${NC} $dep FALTANDO"
    fi
done

echo ""
echo "================================"
echo "  Verificação Concluída"
echo "================================"
echo ""
echo "Se algum arquivo estiver marcado com ✗,"
echo "execute o script de download:"
echo "  ./download-deps.sh"
echo ""
