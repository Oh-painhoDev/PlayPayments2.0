<?php
/**
 * ==========================================
 * TESTE SHARKBANKING - ARQUIVO DE CONFIGURAÇÃO
 * ==========================================
 * 
 * Configure todos os parâmetros abaixo e execute:
 * php artisan test:sharkbanking --user_id=1
 * 
 * OU use as opções:
 * php artisan test:sharkbanking --user_id=1 --amount=150.00 --sale_name="Meu Produto"
 */

// ==========================================
// CONFIGURAÇÕES DO USUÁRIO
// ==========================================
$CONFIG = [
    // ID do usuário (OBRIGATÓRIO)
    'user_id' => 1,
    
    // ID do gateway (opcional - usa gateway padrão do usuário se não informado)
    'gateway_id' => null,
    
    // ==========================================
    // CONFIGURAÇÕES DA TRANSAÇÃO
    // ==========================================
    
    // Valor da transação em reais
    'amount' => 100.00,
    
    // Método de pagamento: pix, credit_card, bank_slip
    'payment_method' => 'pix',
    
    // ==========================================
    // INFORMAÇÕES DO PRODUTO/VENDA
    // ==========================================
    
    // Nome da venda/produto (OBRIGATÓRIO)
    'sale_name' => 'Produto Teste',
    
    // Descrição da venda/produto
    'description' => 'Descrição detalhada do produto de teste para validação da integração SharkBanking',
    
    // ==========================================
    // CONFIGURAÇÕES DO PIX
    // ==========================================
    
    // Opção 1: Usar MINUTOS (para valores < 24 horas)
    // Escolha entre: 15, 30, 60, 120, 180, 360, 720, 1440 minutos
    'pix_expires_in_minutes' => 15,
    
    // Opção 2: Usar DIAS (para valores >= 1 dia)
    // Digite o número de dias (1 a 90)
    // Se preenchido, será usado ao invés de pix_expires_in_minutes
    'pix_expires_in_days' => null, // Exemplo: 7 para 7 dias, 30 para 30 dias
    
    // ==========================================
    // DADOS DO CLIENTE
    // ==========================================
    
    'customer' => [
        'name' => 'João Silva',
        'email' => 'joao.silva@example.com',
        'document' => '12345678900', // CPF ou CNPJ (apenas números)
        'phone' => '11999999999', // Telefone (apenas números)
    ],
    
    // ==========================================
    // CONFIGURAÇÕES AVANÇADAS
    // ==========================================
    
    'installments' => 1, // Parcelas (apenas para cartão de crédito)
    'redirect_url' => null, // URL de redirecionamento após pagamento
];

// ==========================================
// EXEMPLOS DE USO
// ==========================================

/*

// EXEMPLO 1: PIX com 15 minutos de expiração
$CONFIG = [
    'user_id' => 1,
    'amount' => 50.00,
    'payment_method' => 'pix',
    'sale_name' => 'Curso Online',
    'description' => 'Acesso ao curso completo por 30 dias',
    'pix_expires_in_minutes' => 15,
    'pix_expires_in_days' => null,
    'customer' => [
        'name' => 'Maria Santos',
        'email' => 'maria@example.com',
        'document' => '98765432100',
        'phone' => '11988888888',
    ],
];

// EXEMPLO 2: PIX com 7 dias de expiração
$CONFIG = [
    'user_id' => 1,
    'amount' => 250.00,
    'payment_method' => 'pix',
    'sale_name' => 'Assinatura Mensal',
    'description' => 'Plano mensal de serviços premium',
    'pix_expires_in_minutes' => null,
    'pix_expires_in_days' => 7, // 7 dias
    'customer' => [
        'name' => 'Carlos Oliveira',
        'email' => 'carlos@example.com',
        'document' => '11122233344',
        'phone' => '11977777777',
    ],
];

// EXEMPLO 3: PIX com 30 dias de expiração
$CONFIG = [
    'user_id' => 1,
    'amount' => 500.00,
    'payment_method' => 'pix',
    'sale_name' => 'Produto Premium',
    'description' => 'Licença anual do software premium',
    'pix_expires_in_minutes' => null,
    'pix_expires_in_days' => 30, // 30 dias
    'customer' => [
        'name' => 'Ana Costa',
        'email' => 'ana@example.com',
        'document' => '55566677788',
        'phone' => '11966666666',
    ],
];

// EXEMPLO 4: Cartão de Crédito
$CONFIG = [
    'user_id' => 1,
    'amount' => 300.00,
    'payment_method' => 'credit_card',
    'sale_name' => 'Combo Premium',
    'description' => 'Pacote completo com todos os recursos',
    'installments' => 3, // 3x sem juros
    'customer' => [
        'name' => 'Pedro Almeida',
        'email' => 'pedro@example.com',
        'document' => '99988877766',
        'phone' => '11955555555',
    ],
];

*/

// ==========================================
// INSTRUÇÕES
// ==========================================

/*
 * COMO USAR:
 * 
 * 1. Configure os parâmetros acima ($CONFIG)
 * 
 * 2. Execute o comando:
 *    php artisan test:sharkbanking --user_id=1
 * 
 * 3. OU passe os parâmetros diretamente:
 *    php artisan test:sharkbanking \
 *        --user_id=1 \
 *        --amount=150.00 \
 *        --payment_method=pix \
 *        --sale_name="Meu Produto" \
 *        --description="Descrição do produto" \
 *        --pix_expires_in_days=7 \
 *        --customer_name="João Silva" \
 *        --customer_email="joao@example.com" \
 *        --customer_document="12345678900" \
 *        --customer_phone="11999999999"
 * 
 * 4. O comando irá:
 *    - Validar os dados
 *    - Exibir um resumo
 *    - Pedir confirmação
 *    - Criar a transação
 *    - Exibir os resultados (QR Code, payload, etc)
 * 
 * 5. IMPORTANTE - Expiração PIX:
 *    - Use pix_expires_in_minutes para valores < 24 horas (15 min a 1439 min)
 *    - Use pix_expires_in_days para valores >= 1 dia (1 a 90 dias)
 *    - Se pix_expires_in_days for preenchido, será usado ao invés de minutes
 *    - A API SharkBanking usa:
 *      * expiresIn (segundos) para valores < 1 dia
 *      * expiresInDays (dias inteiros) para valores >= 1 dia
 */

echo "📋 Este é um arquivo de configuração de exemplo.\n";
echo "📋 Configure a variável \$CONFIG acima e execute:\n";
echo "📋 php artisan test:sharkbanking --user_id=1\n\n";
echo "📋 Ou veja as instruções completas no arquivo acima.\n";




