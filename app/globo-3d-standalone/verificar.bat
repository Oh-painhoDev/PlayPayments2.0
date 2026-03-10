@echo off
REM Verificar integridade da instalação

setlocal enabledelayedexpansion

cls
echo.
echo ================================
echo    Verificador do Globo 3D
echo ================================
echo.

set "allOk=1"

echo Verificando estrutura...

if exist "index.html" (
    echo [OK] index.html
) else (
    echo [FALTA] index.html
    set "allOk=0"
)

if exist "css\style.css" (
    echo [OK] css\style.css
) else (
    echo [FALTA] css\style.css
    set "allOk=0"
)

if exist "js\app.js" (
    echo [OK] js\app.js
) else (
    echo [FALTA] js\app.js
    set "allOk=0"
)

if exist "package.json" (
    echo [OK] package.json
) else (
    echo [FALTA] package.json
    set "allOk=0"
)

echo.
echo Verificando dependências...

if exist "lib\three.min.js" (
    echo [OK] lib\three.min.js
) else (
    echo [FALTA] lib\three.min.js
    set "allOk=0"
)

if exist "lib\globe.gl.min.js" (
    echo [OK] lib\globe.gl.min.js
) else (
    echo [FALTA] lib\globe.gl.min.js
    set "allOk=0"
)

if exist "img\earth-dark.jpg" (
    echo [OK] img\earth-dark.jpg
) else (
    echo [FALTA] img\earth-dark.jpg
    set "allOk=0"
)

if exist "img\earth-topology.png" (
    echo [OK] img\earth-topology.png
) else (
    echo [FALTA] img\earth-topology.png
    set "allOk=0"
)

if exist "img\night-sky.png" (
    echo [OK] img\night-sky.png
) else (
    echo [FALTA] img\night-sky.png
    set "allOk=0"
)

echo.
echo ================================

if !allOk! equ 1 (
    echo Status: TUDO OK - Pronto para usar!
    echo.
    echo Inicie o servidor:
    echo   php -S localhost:8000
    echo.
    echo Depois acesse: http://localhost:8000
) else (
    echo Status: ALGO FALTANDO
    echo.
    echo Execute o script de download:
    echo   download-deps.bat
)

echo ================================
echo.
pause
