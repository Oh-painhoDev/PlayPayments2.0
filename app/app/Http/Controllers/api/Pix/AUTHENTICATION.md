# Autenticação API PIX v1

## Para Gerar PIX - Requer AMBOS os Tokens

Para criar uma transação PIX (POST `/api/v1/transactions`), você **DEVE** fornecer **AMBOS** os tokens:
- **Public Key** (PB-playpayments-...)
- **Private Key** (SK-playpayments-...) também conhecida como Secret Key

## Formas de Autenticação

### Opção 1: Headers Separados (Recomendado)

Envie os dois tokens em headers separados:

```bash
curl -X POST "https://seu-dominio.com/api/v1/transactions" \
  -H "X-Public-Key: PB-playpayments-seu-public-key-aqui" \
  -H "X-Private-Key: SK-playpayments-seu-private-key-aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 10.00,
    "payment_method": "pix",
    "customer": {
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900"
    }
  }'
```

### Opção 2: Header Authorization com Dois Tokens

Envie os dois tokens no header Authorization, separados por `:`:

```bash
curl -X POST "https://seu-dominio.com/api/v1/transactions" \
  -H "Authorization: Bearer PB-playpayments-seu-public-key-aqui:SK-playpayments-seu-private-key-aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 10.00,
    "payment_method": "pix",
    "customer": {
      "name": "João Silva",
      "email": "joao@example.com",
      "document": "12345678900"
    }
  }'
```

### Opção 3: Headers Alternativos

Também aceita os seguintes nomes de headers:
- `Public-Key` ou `X-Public-Key`
- `Private-Key` ou `X-Private-Key` ou `X-Secret-Key` ou `Secret-Key`

## Para Consultar Transações - Aceita Um Token

Para listar (GET) ou buscar (GET) transações, você pode usar apenas um dos tokens:

### Com Public Key (somente leitura)

```bash
curl -X GET "https://seu-dominio.com/api/v1/transactions" \
  -H "Authorization: Bearer PB-playpayments-seu-public-key-aqui"
```

### Com Private Key (leitura e escrita)

```bash
curl -X GET "https://seu-dominio.com/api/v1/transactions" \
  -H "Authorization: Bearer SK-playpayments-seu-private-key-aqui"
```

## Headers Suportados

### Para Criar PIX (POST) - Requer AMBOS:

| Header | Descrição | Exemplo |
|--------|-----------|---------|
| `X-Public-Key` | Public Key do usuário | `PB-playpayments-abc123...` |
| `X-Private-Key` | Private Key (Secret Key) do usuário | `SK-playpayments-xyz789...` |
| `Public-Key` | Alternativa para Public Key | `PB-playpayments-abc123...` |
| `X-Secret-Key` | Alternativa para Private Key | `SK-playpayments-xyz789...` |
| `Authorization` | Formato: `Bearer public_key:private_key` | `Bearer PB-playpayments-...:SK-playpayments-...` |

### Para Consultar (GET) - Aceita UM:

| Header | Descrição | Exemplo |
|--------|-----------|---------|
| `Authorization` | Bearer token com Public Key ou Private Key | `Bearer PB-playpayments-...` ou `Bearer SK-playpayments-...` |

## Exemplos em Diferentes Linguagens

### JavaScript/Node.js (Axios)

```javascript
const axios = require('axios');

// Criar PIX - Requer ambos os tokens
const response = await axios.post('https://seu-dominio.com/api/v1/transactions', {
  amount: 10.00,
  payment_method: 'pix',
  customer: {
    name: 'João Silva',
    email: 'joao@example.com',
    document: '12345678900'
  }
}, {
  headers: {
    'X-Public-Key': 'PB-playpayments-seu-public-key-aqui',
    'X-Private-Key': 'SK-playpayments-seu-private-key-aqui',
    'Content-Type': 'application/json'
  }
});
```

### PHP (cURL)

```php
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://seu-dominio.com/api/v1/transactions');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-Public-Key: PB-playpayments-seu-public-key-aqui',
    'X-Private-Key: SK-playpayments-seu-private-key-aqui',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'amount' => 10.00,
    'payment_method' => 'pix',
    'customer' => [
        'name' => 'João Silva',
        'email' => 'joao@example.com',
        'document' => '12345678900'
    ]
]));

$response = curl_exec($ch);
curl_close($ch);
```

### Python (Requests)

```python
import requests

# Criar PIX - Requer ambos os tokens
response = requests.post(
    'https://seu-dominio.com/api/v1/transactions',
    json={
        'amount': 10.00,
        'payment_method': 'pix',
        'customer': {
            'name': 'João Silva',
            'email': 'joao@example.com',
            'document': '12345678900'
        }
    },
    headers={
        'X-Public-Key': 'PB-playpayments-seu-public-key-aqui',
        'X-Private-Key': 'SK-playpayments-seu-private-key-aqui',
        'Content-Type': 'application/json'
    }
)
```

## Resposta de Erro

Se você tentar criar um PIX sem fornecer ambos os tokens, receberá:

```json
{
  "success": false,
  "error": "Ambos os tokens são necessários para criar PIX. Forneça Public Key e Private Key nos headers: X-Public-Key e X-Private-Key",
  "required_headers": {
    "X-Public-Key": "Public Key (PB-playpayments-...)",
    "X-Private-Key": "Private Key (SK-playpayments-...) ou X-Secret-Key"
  }
}
```

## Segurança

1. **Nunca exponha sua Private Key** em código frontend ou código público
2. **Mantenha suas chaves seguras** - trate-as como senhas
3. **Use Public Key** apenas para consultas (GET) quando possível
4. **Use Private Key** apenas no backend ou em ambientes seguros
5. **Para criar PIX**, sempre use **AMBOS** os tokens

## Obter suas Chaves

Você pode obter suas chaves API no painel do sistema:
- Acesse: `Configurações` > `API`
- Sua **Public Key** começa com `PB-playpayments-`
- Sua **Private Key** (Secret Key) começa com `SK-playpayments-`

## Notas Importantes

- **Public Key**: Pode ser usada para consultas (GET) apenas
- **Private Key**: Pode ser usada para consultas (GET) e criação (POST)
- **Para criar PIX**: Você **DEVE** fornecer **AMBOS** os tokens
- Os tokens são únicos por usuário e não podem ser compartilhados

