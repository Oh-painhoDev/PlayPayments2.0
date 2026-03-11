# Correção: Extração do Código PIX (EMV) da Pluggou

## Problema
- Erro: `Class "SimpleSoftwareIO\QrCode\Facades\QrCode" not found`
- Erro: `UserGatewayCredential: Erro ao descriptografar secret key`
- Código PIX (EMV) não estava sendo extraído corretamente do JSON da resposta da Pluggou

## Soluções Implementadas

### 1. Remoção da Dependência QrCode (já estava feito)
- Todas as referências ao `SimpleSoftwareIO\QrCode\Facades\QrCode` foram removidas
- O QR Code agora é gerado no frontend usando a biblioteca `qrcode.js` (via CDN)
- Isso evita problemas de instalação e funciona perfeitamente

### 2. Melhoria na Extração do EMV no Backend

#### `PluggouGatewayService.php`
- **Múltiplos campos suportados**: A extração agora tenta os seguintes campos na ordem de prioridade:
  1. `data.pix.emv` (campo padrão da Pluggou conforme documentação)
  2. `data.pix.qrcode`
  3. `data.pix.payload`
  4. `data.pix.code`
  5. `data.emv` (direto no data)
  6. `data.pix_code` (direto no data)

- **Logs detalhados**: Adicionados logs que mostram:
  - Qual campo foi usado para extrair o EMV
  - Comprimento do código EMV
  - Pré-visualização do código (primeiros 50 caracteres)
  - Todos os campos verificados e seu status (found/empty/not_set)

- **Validação robusta**: Verifica se `data` e `data.pix` são arrays antes de acessar
- **Mensagens de erro melhoradas**: Mensagens de erro agora incluem a estrutura recebida para facilitar o debug

#### `DepositController.php`
- **Extração melhorada**: Tenta extrair o EMV de `payment_data.pix.emv`, `payment_data.pix.payload`, `payment_data.pix.qrcode`, `payment_data.pix.code`, `gateway_response.emv`, e `gateway_response.pix_code`
- **Logs detalhados**: Logs mostram qual campo foi usado e todos os campos verificados
- **Resposta padronizada**: A resposta sempre inclui o código EMV em `pix.emv`, `pix.payload`, `pix.qrcode`, e `pix.code` para compatibilidade

### 3. Melhoria na Extração do EMV no Frontend

#### `dashboard.blade.php`
- **Múltiplos campos suportados**: O JavaScript agora tenta os seguintes campos na ordem de prioridade:
  1. `data.pix.emv`
  2. `data.pix.payload`
  3. `data.pix.qrcode`
  4. `data.pix.code`
  5. `data.pix_code`
  6. `data.emv`

- **Logs no console**: Adicionados `console.log` e `console.error` para facilitar o debug
- **Mensagens de erro melhoradas**: Mensagens de erro agora mostram a estrutura recebida

### 4. Script de Diagnóstico de Credenciais

#### `public/fix-pluggou-credentials.php`
- Novo script para diagnosticar problemas com credenciais da Pluggou
- Verifica:
  - Se as credenciais existem
  - Se a Public Key está presente
  - Se a Secret Key (raw/encrypted) está presente
  - Se a Secret Key pode ser descriptografada
  - Se as credenciais estão ativas
  - Se está em sandbox ou produção
- Fornece instruções específicas para corrigir problemas encontrados
- Acesse: `https://seudominio.com/fix-pluggou-credentials.php?gateway_id=X`

### 5. Tratamento de Erros de Descriptografia

#### `UserGatewayCredential.php`
- **Tratamento específico de `DecryptException`**: Captura especificamente erros de descriptografia
- **Mensagens informativas**: Logs agora indicam que o problema pode ser:
  - APP_KEY mudou
  - Credencial está corrompida
  - Solução: Re-salvar as credenciais no painel administrativo

## Como Usar

### 1. Verificar Credenciais
Acesse: `https://seudominio.com/fix-pluggou-credentials.php?gateway_id=X`
(Substitua `X` pelo ID do gateway Pluggou)

### 2. Se as Credenciais Estiverem Corrompidas
1. Vá para Admin > Gateways
2. Clique em "Editar" nas credenciais do gateway
3. Cole novamente a Public Key e Secret Key
4. Salve as credenciais

### 3. Verificar Logs
Os logs agora incluem informações detalhadas sobre:
- Estrutura da resposta da API Pluggou
- Campo usado para extrair o EMV
- Comprimento do código EMV
- Todos os campos verificados

## Estrutura Esperada da Resposta da Pluggou

A API Pluggou deve retornar uma estrutura como:

```json
{
  "success": true,
  "message": "Transação criada com sucesso",
  "data": {
    "id": "transaction_id",
    "amount": 10000,
    "status": "pending",
    "pix": {
      "emv": "00020126860014br.gov.bcb.pix2564..."
    }
  }
}
```

O código agora suporta múltiplas variações desta estrutura, garantindo compatibilidade mesmo se a API mudar levemente.

## Próximos Passos

1. Testar a geração de PIX de depósito
2. Verificar os logs para confirmar que o EMV está sendo extraído corretamente
3. Se houver problemas, usar o script de diagnóstico para identificar a causa
4. Se as credenciais estiverem corrompidas, re-salvá-las no painel administrativo

## Notas Importantes

- O QR Code é gerado no frontend usando `qrcode.js`, não no backend
- O código EMV é extraído de múltiplos campos possíveis para garantir compatibilidade
- Logs detalhados foram adicionados para facilitar o debug
- Credenciais corrompidas podem ser diagnosticadas e corrigidas usando o script de diagnóstico




