// app/Services/PodPayService.php
namespace App\Services;

use App\Models\Acquirer;
use Illuminate\Support\Facades\Http;

class PodPayService
{
    protected $acquirer;
    
    public function __construct(Acquirer $acquirer)
    {
        $this->acquirer = $acquirer;
    }

    /**
     * Cria pagamento via PIX
     */
    public function createPayment(float $amount, array $customer): array
    {
        // Verificar se o valor está acima do mínimo de R$ 5,00
        if ($amount < $this->acquirer->min_pix_amount) {
            return ['error' => 'Valor mínimo para PIX é R$ ' . $this->acquirer->min_pix_amount];
        }

        // Convertendo o valor para centavos
        $amountCents = intval(round($amount * 100));

        $payload = [
            "amount" => $amountCents,
            "currency" => "BRL",
            "paymentMethod" => "pix",
            "items" => [
                [
                    "title" => $customer['description'] ?? "Créditos",
                    "quantity" => 1,
                    "unitPrice" => $amountCents,
                    "tangible" => false
                ]
            ],
            "customer" => [
                "name" => $customer['name'],
                "email" => $customer['email'],
                "document" => [
                    "type" => "cpf",
                    "number" => $customer['document']
                ],
                "phone" => $customer['phone']
            ],
            "pix" => [
                "expiresIn" => $customer['expiresIn'] ?? 3600 // Expiração do PIX em segundos
            ],
            "postbackUrl" => $customer['postbackUrl'] ?? null,
            "returnUrl" => $customer['returnUrl'] ?? null,
            "externalRef" => $customer['externalRef'] ?? "venda_" . time() . rand(100, 999),
            "ip" => request()->ip() // IP do cliente
        ];

        // Faz a requisição HTTP para a adquirente
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode("{$this->acquirer->public_key}:{$this->acquirer->secret_key}")
        ])
        ->post("{$this->acquirer->api_url}/v1/transactions", $payload);

        if ($response->failed()) {
            return ['error' => $response->body()];
        }

        $data = $response->json();

        if (isset($data['error'])) {
            return ['error' => $data['error']];
        }

        return [
            'transactionId' => $data['id'],
            'qrcode' => $data['pix']['payload'] ?? $data['pix']['copyPasteKey'] ?? $data['pix']['qrcode'],
            'externalRef' => $data['externalRef'] ?? null,
            'status' => $data['status'] ?? 'PENDING',
        ];
    }
}
