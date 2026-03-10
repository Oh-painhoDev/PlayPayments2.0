# ============================================
# TESTE WEBHOOK PLUGGOU - PowerShell
# ============================================

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "TESTE WEBHOOK PLUGGOU" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

$webhookUrl = "https://playpayments.com/webhook/pluggou"

Write-Host "URL: $webhookUrl" -ForegroundColor Yellow
Write-Host ""

# Dados do webhook (simulando Pluggou)
$webhookData = @{
    id = "test-webhook-$(Get-Random)"
    event_type = "transaction"
    data = @{
        id = "f5ef6662-b893-4080-8xxx"
        payment_method = "pix"
        e2e_id = "E60701190202507071554DY5IQBAOH2C"
        amount = 300
        platform_tax = 206
        liquid_amount = 95
        status = "paid"
        paid_at = (Get-Date -Format "yyyy-MM-dd HH:mm:ss")
        created_at = (Get-Date).AddMinutes(-3).ToString("yyyy-MM-dd HH:mm:ss")
    }
}

Write-Host "📤 Enviando webhook..." -ForegroundColor Green
Write-Host ""

# Converter para JSON
$jsonBody = $webhookData | ConvertTo-Json -Depth 10

try {
    # Fazer requisição POST
    $response = Invoke-RestMethod -Uri $webhookUrl `
        -Method Post `
        -ContentType "application/json" `
        -Body $jsonBody `
        -ErrorAction Stop

    Write-Host "✅ SUCESSO!" -ForegroundColor Green
    Write-Host ""
    Write-Host "📥 Resposta:" -ForegroundColor Cyan
    $response | ConvertTo-Json -Depth 10 | Write-Host
    
} catch {
    Write-Host "❌ ERRO!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Mensagem: $($_.Exception.Message)" -ForegroundColor Red
    
    if ($_.Exception.Response) {
        $statusCode = [int]$_.Exception.Response.StatusCode
        Write-Host "HTTP Code: $statusCode" -ForegroundColor Yellow
        
        # Tentar ler o corpo da resposta
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        $reader.Close()
        
        Write-Host ""
        Write-Host "Resposta:" -ForegroundColor Yellow
        Write-Host $responseBody
    }
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Teste concluído!" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

Read-Host "Pressione Enter para sair"








