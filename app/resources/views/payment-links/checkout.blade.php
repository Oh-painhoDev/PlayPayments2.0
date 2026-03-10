<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - {{ $paymentLink->title }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-[#161616] rounded-2xl border border-[#2d2d2d] p-6 shadow-2xl">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-white mb-2">{{ $paymentLink->title }}</h1>
            @if($paymentLink->description)
                <p class="text-[#AAAAAA] text-sm">{{ $paymentLink->description }}</p>
            @endif
        </div>

        <form id="checkoutForm" class="space-y-4">
            @if($paymentLink->allow_custom_amount || !$paymentLink->amount)
                <div>
                    <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">Valor (R$)</label>
                    <input type="text" id="amount" name="amount" required 
                           class="w-full px-4 py-3 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-[#D4AF37] placeholder-[#707070]"
                           placeholder="0,00" 
                           oninput="formatCurrency(this)"
                           @if($paymentLink->min_amount) data-min="{{ $paymentLink->min_amount }}" @endif
                           @if($paymentLink->max_amount) data-max="{{ $paymentLink->max_amount }}" @endif>
                    @if($paymentLink->min_amount || $paymentLink->max_amount)
                        <p class="text-xs text-[#707070] mt-1">
                            @if($paymentLink->min_amount) Mínimo: R$ {{ number_format($paymentLink->min_amount, 2, ',', '.') }} @endif
                            @if($paymentLink->min_amount && $paymentLink->max_amount) • @endif
                            @if($paymentLink->max_amount) Máximo: R$ {{ number_format($paymentLink->max_amount, 2, ',', '.') }} @endif
                        </p>
                    @endif
                </div>
            @else
                <div class="bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg p-4 text-center">
                    <p class="text-sm text-[#707070] mb-1">Valor</p>
                    <p class="text-3xl font-bold text-white">R$ {{ number_format($paymentLink->amount, 2, ',', '.') }}</p>
                </div>
            @endif

            @if($paymentLink->payment_method === 'all')
                <div>
                    <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">Forma de Pagamento</label>
                    <select id="payment_method" name="payment_method" required 
                            class="w-full px-4 py-3 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37]">
                        <option value="pix">PIX</option>
                        <option value="credit_card">Cartão de Crédito</option>
                        <option value="bank_slip">Boleto</option>
                    </select>
                </div>
            @else
                <input type="hidden" id="payment_method" name="payment_method" value="{{ $paymentLink->payment_method }}">
            @endif

            <div class="border-t border-[#2d2d2d] pt-4 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">Nome Completo *</label>
                    <input type="text" id="customer_name" name="customer[name]" required 
                           class="w-full px-4 py-3 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37] placeholder-[#707070]"
                           placeholder="Seu nome completo">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">E-mail *</label>
                    <input type="email" id="customer_email" name="customer[email]" required 
                           class="w-full px-4 py-3 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37] placeholder-[#707070]"
                           placeholder="seu@email.com">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">CPF/CNPJ *</label>
                    <input type="text" id="customer_document" name="customer[document]" required 
                           class="w-full px-4 py-3 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37] placeholder-[#707070]"
                           placeholder="000.000.000-00"
                           oninput="formatDocument(this)">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">Telefone</label>
                    <input type="text" id="customer_phone" name="customer[phone]" 
                           class="w-full px-4 py-3 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37] placeholder-[#707070]"
                           placeholder="(00) 00000-0000"
                           oninput="formatPhone(this)">
                </div>
            </div>

            <div id="errorMessage" class="hidden bg-[#ff6b6b] bg-opacity-10 border border-[#ff6b6b] rounded-lg p-3 text-[#ff6b6b] text-sm"></div>

            <button type="submit" id="submitBtn" 
                    class="w-full bg-[#D4AF37] hover:bg-[#D4AF37] text-white font-semibold py-3 rounded-lg transition-colors">
                Finalizar Pagamento
            </button>
        </form>

        <div id="paymentResult" class="hidden mt-6">
            <div class="bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg p-6 text-center">
                <div id="pixPayment" class="hidden">
                    <h3 class="text-lg font-semibold text-white mb-4">Pagamento via PIX</h3>
                    <div id="qrCode" class="mb-4 flex justify-center"></div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">Código PIX (Copiar e Colar)</label>
                        <div class="flex gap-2">
                            <input type="text" id="pixCode" readonly 
                                   class="flex-1 px-4 py-2 bg-[#161616] border border-[#2d2d2d] rounded-lg text-white text-xs font-mono">
                            <button onclick="copyPixCode()" 
                                    class="px-4 py-2 bg-[#D4AF37] hover:bg-[#D4AF37] text-white rounded-lg text-sm font-semibold transition-colors">
                                Copiar
                            </button>
                        </div>
                    </div>
                </div>
                <div id="otherPayment" class="hidden">
                    <p class="text-white">Redirecionando para o pagamento...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
        function formatCurrency(input) {
            let value = input.value.replace(/\D/g, '');
            value = (value / 100).toFixed(2) + '';
            value = value.replace(".", ",");
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            input.value = value;
        }

        function formatDocument(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            }
            input.value = value;
        }

        function formatPhone(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length <= 10) {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            } else {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
            }
            input.value = value;
        }

        function copyPixCode() {
            const pixCode = document.getElementById('pixCode');
            pixCode.select();
            document.execCommand('copy');
            alert('Código PIX copiado!');
        }

        document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const errorMessage = document.getElementById('errorMessage');
            const paymentResult = document.getElementById('paymentResult');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processando...';
            errorMessage.classList.add('hidden');
            
            const formData = new FormData(this);
            const data = {
                amount: formData.get('amount') ? formData.get('amount').replace(/\./g, '').replace(',', '.') : '{{ $paymentLink->amount }}',
                payment_method: formData.get('payment_method'),
                customer: {
                    name: formData.get('customer[name]'),
                    email: formData.get('customer[email]'),
                    document: formData.get('customer[document]').replace(/\D/g, ''),
                    phone: formData.get('customer[phone]') ? formData.get('customer[phone]').replace(/\D/g, '') : null
                }
            };
            
            try {
                const response = await fetch('{{ route("checkout.process", $paymentLink->slug) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.style.display = 'none';
                    paymentResult.classList.remove('hidden');
                    
                    if (data.payment_method === 'pix') {
                        // Check for qrcode in multiple possible locations
                        const qrcode = result.payment_data?.qrcode || 
                                     result.payment_data?.payload || 
                                     result.payment_data?.pix?.qrcode ||
                                     result.payment_data?.pix?.payload ||
                                     result.pix_code ||
                                     result.qr_code;
                        
                        if (qrcode) {
                            document.getElementById('pixPayment').classList.remove('hidden');
                            document.getElementById('pixCode').value = qrcode;
                            
                            QRCode.toCanvas(document.getElementById('qrCode'), qrcode, {
                                width: 200,
                                margin: 2,
                                color: {
                                    dark: '#000000',
                                    light: '#FFFFFF'
                                }
                            }, function (error) {
                                if (error) {
                                    console.error('Erro ao gerar QR Code:', error);
                                    errorMessage.textContent = 'Erro ao gerar QR Code. O código PIX está disponível para copiar abaixo.';
                                    errorMessage.classList.remove('hidden');
                                }
                            });
                        } else {
                            // No QR code found
                            errorMessage.textContent = 'Erro: QR Code PIX não foi gerado. Por favor, tente novamente ou entre em contato com o suporte.';
                            errorMessage.classList.remove('hidden');
                            this.style.display = 'block';
                            paymentResult.classList.add('hidden');
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Finalizar Pagamento';
                            console.error('PIX data not found:', result);
                        }
                    } else {
                        document.getElementById('otherPayment').classList.remove('hidden');
                        if (result.payment_data && result.payment_data.redirect_url) {
                            window.location.href = result.payment_data.redirect_url;
                        } else {
                            errorMessage.textContent = 'Redirecionamento não disponível. Verifique o status do pagamento.';
                            errorMessage.classList.remove('hidden');
                        }
                    }
                } else {
                    errorMessage.textContent = result.message || 'Erro ao processar pagamento. Tente novamente.';
                    errorMessage.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Finalizar Pagamento';
                }
            } catch (error) {
                console.error('Error:', error);
                errorMessage.textContent = 'Erro ao processar pagamento. Tente novamente.';
                errorMessage.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Finalizar Pagamento';
            }
        });
    </script>
</body>
</html>

