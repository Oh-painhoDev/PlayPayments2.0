@echo off
REM Script para baixar as dependências do Globo 3D
REM Execute este arquivo com direitos de administrador

echo ====================================
echo Globo 3D - Baixar Dependências
echo ====================================
echo.

cd /d "%~dp0"

REM Criar pasta lib se não existir
if not exist "lib" mkdir lib
if not exist "img" mkdir img

echo Baixando biblioteca Three.js...
powershell -Command "(New-Object System.Net.WebClient).DownloadFile('https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js', 'lib/three.min.js')"

echo Baixando biblioteca Globe.gl...
powershell -Command "(New-Object System.Net.WebClient).DownloadFile('https://cdn.jsdelivr.net/npm/globe.gl@2.42.0/dist/globe.gl.min.js', 'lib/globe.gl.min.js')"

echo Baixando texturas do globo...
powershell -Command "(New-Object System.Net.WebClient).DownloadFile('https://cdn.jsdelivr.net/npm/three-globe/example/img/earth-dark.jpg', 'img/earth-dark.jpg')"
powershell -Command "(New-Object System.Net.WebClient).DownloadFile('https://cdn.jsdelivr.net/npm/three-globe/example/img/earth-topology.png', 'img/earth-topology.png')"
powershell -Command "(New-Object System.Net.WebClient).DownloadFile('https://cdn.jsdelivr.net/npm/three-globe/example/img/night-sky.png', 'img/night-sky.png')"

echo.
echo ====================================
echo Download concluído!
echo ====================================
echo.
echo Verifique se os arquivos foram criados em:
echo - lib/three.min.js
echo - lib/globe.gl.min.js
echo - img/earth-dark.jpg
echo - img/earth-topology.png
echo - img/night-sky.png
echo.
echo Agora você pode abrir index.html no navegador ou via servidor.
echo.
pause
