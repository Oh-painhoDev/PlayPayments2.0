@echo off
REM ============================================
REM TESTE WEBHOOK PLUGGOU - CMD/PowerShell
REM ============================================

echo.
echo ============================================
echo TESTE WEBHOOK PLUGGOU
echo ============================================
echo.

set WEBHOOK_URL=https://playpayments.com/webhook/pluggou

echo URL: %WEBHOOK_URL%
echo.

REM Criar arquivo JSON temporário com dados do webhook
echo Criando dados do webhook...

(
echo {
echo   "id": "test-webhook-%RANDOM%",
echo   "event_type": "transaction",
echo   "data": {
echo     "id": "f5ef6662-b893-4080-8xxx",
echo     "payment_method": "pix",
echo     "e2e_id": "E60701190202507071554DY5IQBAOH2C",
echo     "amount": 300,
echo     "platform_tax": 206,
echo     "liquid_amount": 95,
echo     "status": "paid",
echo     "paid_at": "2025-11-12 10:30:00",
echo     "created_at": "2025-11-12 10:27:00"
echo   }
echo }
) > webhook_data.json

echo.
echo Enviando webhook...
echo.

REM Usar curl (Windows 10+ tem curl nativo)
curl -X POST "%WEBHOOK_URL%" ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d @webhook_data.json ^
  -w "\n\nHTTP Code: %%{http_code}\n" ^
  -s -S

echo.
echo ============================================
echo Teste concluido!
echo ============================================
echo.

REM Limpar arquivo temporário
del webhook_data.json 2>nul

pause








