# Configuração do Subdomínio API no Hostinger

## Passo 1: Criar Subdomínio no Painel Hostinger

1. Acesse o painel do Hostinger (hPanel)
2. Vá em **Domínios** → **Subdomínios**
3. Clique em **Criar Subdomínio**
4. Preencha:
   - **Nome do subdomínio**: `api`
   - **Domínio principal**: `meudominio.com` (ou seu domínio)
   - **Diretório**: `/public_html` (ou `/htdocs` dependendo da sua configuração)
5. Clique em **Criar**

## Passo 2: Configurar DNS (se necessário)

O Hostinger geralmente configura automaticamente, mas verifique:

- **Tipo**: A ou CNAME
- **Nome**: `api`
- **Valor**: IP do servidor ou domínio principal

## Passo 3: Verificar Configuração do Laravel

O Laravel já está configurado para aceitar requisições do subdomínio `api`. 

As rotas da API estão em `routes/api.php` e são acessíveis via:
- `https://api.meudominio.com/api/payments`
- `https://api.meudominio.com/api/withdrawals`

## Passo 4: Testar

```bash
curl -X GET https://api.meudominio.com/api/payments \
  -H "Authorization: Bearer PB-playpayments-XXXX-XXXX-XXXX"
```

## Importante

- O subdomínio deve apontar para a mesma pasta `public` do Laravel
- Não é necessário criar pasta separada para o subdomínio
- O Laravel detecta automaticamente o subdomínio via `$_SERVER['HTTP_HOST']`
- As rotas da API já estão configuradas e funcionando





