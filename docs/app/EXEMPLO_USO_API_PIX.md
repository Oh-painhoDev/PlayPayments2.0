# Exemplo Prático - API PIX Externa

## 🔐 Autenticação Obrigatória

**IMPORTANTE:** Todas as requisições precisam do token de autenticação para identificar a conta:

```
Authorization: Bearer SK-playpayments-SEU_TOKEN_AQUI
```

## 🎯 Exemplo Completo de Integração

### Passo 1: Criar um PIX

```bash
curl -X POST https://seu-dominio.com/api/external-pix/create \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SK-playpayments-SEU_TOKEN_AQUI" \
  -d '{
    "amount": 50.00,
    "description": "Pagamento de assinatura",
    "customer": {
      "name": "Maria Santos",
      "email": "maria@example.com",
      "document": "98765432100",
      "phone": "11988887777"
    },
    "expires_in": 30,
    "external_id": "ASSINATURA_2024_001",
    "api_url": "https://api.sua-api-pix.com",
    "api_token": "seu_token_aqui",
    "auth_type": "bearer"
  }'
```

### Resposta Esperada:

```json
{
  "success": true,
  "data": {
    "qrcode": "00020126580014br.gov.bcb.pix...",
    "payload": "00020126580014br.gov.bcb.pix...",
    "transaction_id": "TXN_ABC123XYZ",
    "expiration_date": "2024-01-15T14:30:00Z"
  }
}
```

### Passo 2: Exibir o QR Code para o Cliente

Use o campo `qrcode` ou `payload` retornado para gerar o QR Code visual. Você pode usar bibliotecas como:

- **JavaScript**: `qrcode.js`, `qrcode.react`
- **PHP**: `endroid/qr-code`
- **Python**: `qrcode`

### Passo 3: Consultar Status Periodicamente

```bash
curl -X GET "https://seu-dominio.com/api/external-pix/status/TXN_ABC123XYZ?api_url=https://api.sua-api-pix.com&api_token=seu_token&auth_type=bearer" \
  -H "Authorization: Bearer SK-playpayments-SEU_TOKEN_AQUI"
```

### Resposta Esperada:

```json
{
  "success": true,
  "data": {
    "transaction_id": "TXN_ABC123XYZ",
    "status": "paid"
  }
}
```

---

## 💡 Exemplo Completo em JavaScript

```html
<!DOCTYPE html>
<html>
<head>
    <title>Pagamento PIX</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
</head>
<body>
    <h1>Pagamento PIX</h1>
    <div id="qrcode"></div>
    <p id="status">Aguardando pagamento...</p>

    <script>
        // Configurações
        const API_BASE = 'https://seu-dominio.com/api/external-pix';
        const API_SECRET_KEY = 'SK-playpayments-SEU_TOKEN_AQUI'; // OBRIGATÓRIO
        const API_CONFIG = {
            api_url: 'https://api.sua-api-pix.com',
            api_token: 'seu_token_aqui',
            auth_type: 'bearer'
        };

        let transactionId = null;
        let checkInterval = null;

        // Criar PIX
        async function criarPix() {
            try {
                const response = await fetch(`${API_BASE}/create`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${API_SECRET_KEY}`
                    },
                    body: JSON.stringify({
                        amount: 50.00,
                        description: 'Pagamento de assinatura',
                        customer: {
                            name: 'Maria Santos',
                            email: 'maria@example.com',
                            document: '98765432100',
                            phone: '11988887777'
                        },
                        expires_in: 30,
                        external_id: 'ASSINATURA_2024_001',
                        ...API_CONFIG
                    })
                });

                const data = await response.json();

                if (data.success) {
                    transactionId = data.data.transaction_id;
                    
                    // Gerar QR Code visual
                    QRCode.toCanvas(document.getElementById('qrcode'), data.data.qrcode, {
                        width: 300,
                        margin: 2
                    });

                    // Iniciar verificação de status
                    iniciarVerificacaoStatus();
                } else {
                    alert('Erro: ' + data.error);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao criar PIX');
            }
        }

        // Consultar status
        async function consultarStatus() {
            if (!transactionId) return;

            try {
                const url = new URL(`${API_BASE}/status/${transactionId}`);
                url.searchParams.append('api_url', API_CONFIG.api_url);
                url.searchParams.append('api_token', API_CONFIG.api_token);
                url.searchParams.append('auth_type', API_CONFIG.auth_type);

                const response = await fetch(url, {
                    headers: {
                        'Authorization': `Bearer ${API_SECRET_KEY}`
                    }
                });
                const data = await response.json();

                if (data.success) {
                    const status = data.data.status;
                    document.getElementById('status').textContent = 
                        `Status: ${status === 'paid' ? 'Pago!' : 'Aguardando pagamento...'}`;

                    if (status === 'paid') {
                        clearInterval(checkInterval);
                        alert('Pagamento confirmado!');
                    }
                }
            } catch (error) {
                console.error('Erro ao consultar status:', error);
            }
        }

        // Iniciar verificação periódica
        function iniciarVerificacaoStatus() {
            checkInterval = setInterval(consultarStatus, 5000); // Verificar a cada 5 segundos
        }

        // Iniciar quando a página carregar
        window.onload = criarPix;
    </script>
</body>
</html>
```

---

## 🔄 Fluxo Completo de Integração

```
1. Cliente solicita pagamento
   ↓
2. Seu sistema chama: POST /api/external-pix/create
   ↓
3. Recebe QR Code PIX
   ↓
4. Exibe QR Code para o cliente
   ↓
5. Consulta status periodicamente: GET /api/external-pix/status/{id}
   ↓
6. Quando status = "paid", confirma pagamento
```

---

## 📋 Checklist de Implementação

- [ ] Obter token/credenciais da API PIX externa
- [ ] Configurar URL base da API externa
- [ ] Implementar criação de PIX
- [ ] Implementar exibição de QR Code
- [ ] Implementar consulta de status
- [ ] Configurar verificação periódica de status
- [ ] Tratar casos de expiração
- [ ] Implementar tratamento de erros
- [ ] Testar em ambiente de desenvolvimento
- [ ] Testar em produção

---

## 🧪 Teste Rápido

Use este exemplo para testar rapidamente:

```bash
# Substitua os valores abaixo pelos seus dados reais
API_URL="https://seu-dominio.com/api/external-pix"
API_SECRET_KEY="SK-playpayments-SEU_TOKEN_AQUI"  # OBRIGATÓRIO
EXTERNAL_API_URL="https://api.sua-api-pix.com"
EXTERNAL_API_TOKEN="seu_token_aqui"

# Criar PIX
curl -X POST "$API_URL/create" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $API_SECRET_KEY" \
  -d "{
    \"amount\": 10.00,
    \"description\": \"Teste de integração\",
    \"customer\": {
      \"name\": \"Teste Cliente\",
      \"email\": \"teste@example.com\",
      \"document\": \"12345678900\",
      \"phone\": \"11999999999\"
    },
    \"expires_in\": 15,
    \"api_url\": \"$EXTERNAL_API_URL\",
    \"api_token\": \"$EXTERNAL_API_TOKEN\",
    \"auth_type\": \"bearer\"
  }"
```

---

## ❓ Perguntas Frequentes

**P: Posso usar sem configurar no servidor?**  
R: Sim! Você pode enviar as credenciais (`api_url`, `api_token`) em cada requisição.

**P: Qual é o tempo de expiração padrão?**  
R: 15 minutos. Você pode alterar com o parâmetro `expires_in` (em minutos).

**P: Com que frequência devo consultar o status?**  
R: Recomendamos a cada 5-10 segundos enquanto aguarda pagamento.

**P: O que fazer se o PIX expirar?**  
R: Crie um novo PIX com um novo `external_id`.

**P: Posso usar o mesmo external_id duas vezes?**  
R: Não recomendado. Use IDs únicos para cada transação.

---

**Precisa de ajuda? Entre em contato com nosso suporte!**

