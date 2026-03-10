@echo off
REM Script para baixar as dependências do Globo 3D Standalone
REM Execute este arquivo com direitos de administrador

title Globo 3D Standalone - Download de Dependências

cls
echo.
echo ====================================================
echo      GLOBO 3D STANDALONE - DOWNLOAD AUTO
echo ====================================================
echo.

cd /d "%~dp0"

REM Criar pastas se não existir
if not exist "lib" mkdir lib
if not exist "img" mkdir img

echo.
echo [1/5] Criando pastas necessarias...
echo OK - Pastas criadas
echo.

echo [2/5] Baixando Three.js...
powershell -Command "(New-Object System.Net.WebClient).DownloadFile('https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js', 'lib/three.min.js')" && echo OK - Three.js baixado || echo ERRO ao baixar Three.js

echo [3/5] Baixando Globe.gl...
powershell -Command "(New-Object System.Net.WebClient).DownloadFile('https://cdn.jsdelivr.net/npm/globe.gl@2.42.0/dist/globe.gl.min.js', 'lib/globe.gl.min.js')" && echo OK - Globe.gl baixado || echo ERRO ao baixar Globe.gl

echo [4/5] Baixando texturas do globo...
powershell -Command "(New-Object System.Net.WebClient).DownloadFile('https://cdn.jsdelivr.net/npm/three-globe/example/img/earth-dark.jpg', 'img/earth-dark.jpg')" && echo OK - Textura earth-dark baixada || echo ERRO
powershell -Command "(New-Object System.Net.WebClient).DownloadFile('https://cdn.jsdelivr.net/npm/three-globe/example/img/earth-topology.png', 'img/earth-topology.png')" && echo OK - Textura earth-topology baixada || echo ERRO
powershell -Command "(New-Object System.Net.WebClient).DownloadFile('https://cdn.jsdelivr.net/npm/three-globe/example/img/night-sky.png', 'img/night-sky.png')" && echo OK - Textura night-sky baixada || echo ERRO

echo [5/5] Finalizando...
echo.
echo ====================================================
echo      DOWNLOAD CONCLUIDO COM SUCESSO!
echo ====================================================
echo.
echo Arquivos criados em:
echo   - lib/three.min.js
echo   - lib/globe.gl.min.js
echo   - img/earth-dark.jpg
echo   - img/earth-topology.png
echo   - img/night-sky.png
echo.
echo Para iniciar o servidor, execute:
echo   - Windows: php -S localhost:8000
echo   - Python: python -m http.server 8000
echo.
echo Depois acesse: http://localhost:8000
echo.
pause
