@extends('layouts.dashboard')

@section('title', 'Transações')

@section('content')
<div class="flex-1 overflow-y-auto bg-[#000000] p-5">
    <div class="max-w-[1600px] mx-auto">
        <div class="bg-[#000000] rounded-2xl space-y-6">
            <!-- Header -->
            <div class="content-stretch flex flex-col md:flex-row items-start md:items-center justify-start md:justify-between relative size-full p-5 gap-6 md:gap-0">
                <div class="content-stretch flex flex-col gap-2.5 items-start justify-start leading-[0] relative shrink-0 text-nowrap">
                    <div class="font-['Manrope:Regular',_sans-serif] font-normal relative shrink-0 text-[28px] tracking-[-0.56px]">
                        <h1 class="leading-[1.2] text-nowrap whitespace-pre text-white">Transações</h1>
                    </div>
                    <div class="font-['Manrope:SemiBold',_sans-serif] font-regular relative shrink-0 text-[12px] tracking-[-0.24px]">
                        <p class="leading-[1.3] text-nowrap whitespace-pre text-[#AAAAAA]">Gerencie e acompanhe todas as suas transações</p>
                    </div>
                </div>
                
                <!-- Filtro de Data -->
                <div class="box-border content-stretch flex flex-col gap-2.5 items-start justify-center px-4 py-2.5 relative rounded-lg shrink-0 w-full md:w-auto">
                    <div class="grid gap-2 w-full">
                        <form method="GET" action="{{ route('transactions.index') }}" class="flex gap-2">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <input type="hidden" name="status" value="{{ request('status') }}">
                            <input type="hidden" name="payment_method" value="{{ request('payment_method') }}">
                            <input type="date" name="date_from" value="{{ request('date_from', \Carbon\Carbon::now()->subDays(30)->format('Y-m-d')) }}" class="px-3 py-2 bg-[#161616] border border-[#2d2d2d] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#D4AF37]">
                            <span class="px-2 py-2 text-[#AAAAAA] text-sm">até</span>
                            <input type="date" name="date_to" value="{{ request('date_to', \Carbon\Carbon::now()->format('Y-m-d')) }}" class="px-3 py-2 bg-[#161616] border border-[#2d2d2d] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#D4AF37]">
                            <button type="submit" class="px-4 py-2 bg-[#D4AF37] hover:bg-[#D4AF37] text-white rounded-lg text-sm font-medium transition-colors">
                                Aplicar
                            </button>
                        </form>
                </div>
                </div>
            </div>
            
            <!-- Filtros e Busca -->
            <div class="box-border flex flex-col gap-2.5 items-start justify-start p-[20px] relative rounded-2xl shrink-0 w-full bg-[#161616]">
                <div class="content-stretch flex gap-4 items-center justify-start relative shrink-0 w-full">
                    <!-- Campo de Busca -->
                    <div class="basis-0 box-border flex flex-col gap-2.5 grow items-start justify-center min-h-px min-w-px px-4 py-3 relative rounded-lg shrink-0 bg-[#1f1f1f]">
                        <div class="content-stretch flex gap-2.5 items-center justify-end relative shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search h-5 w-5 text-[#aaaaaa]">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                </svg>
                            <div class="flex flex-col gap-1.5">
                                <form method="GET" action="{{ route('transactions.index') }}" class="w-full">
                                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                                    <input type="hidden" name="status" value="{{ request('status') }}">
                                    <input type="hidden" name="payment_method" value="{{ request('payment_method') }}">
                                    <input 
                                        class="flex w-full rounded-md border border-input ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 border-none p-0 h-auto text-[14px] font-normal font-['Manrope:Regular',_sans-serif] tracking-[-0.28px] placeholder:text-[#aaaaaa] focus-visible:ring-0 bg-transparent text-white" 
                                        placeholder="Buscar transações" 
                                        type="text" 
                                        name="search"
                                        value="{{ request('search') }}"
                                        onchange="this.form.submit()"
                                    >
                                </form>
                </div>
            </div>
        </div>

                    <!-- Botão de Filtros -->
                    <div class="flex flex-row items-center self-stretch">
                        <button type="button" class="whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground box-border flex gap-2.5 h-full items-center justify-center px-4 py-2 relative rounded-md shrink-0 bg-[#1f1f1f] button-custom" onclick="toggleFilters()">
                            <div class="content-stretch flex gap-2.5 items-center justify-start relative shrink-0">
                                <div class="content-stretch flex gap-1 items-center justify-start relative shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M12.6667 1.33334H3.33337C2.80294 1.33334 2.29423 1.54406 1.91916 1.91913C1.54409 2.2942 1.33337 2.80291 1.33337 3.33334V4.11334C1.33328 4.38864 1.39002 4.66099 1.50004 4.91334V4.95334C1.59409 5.1674 1.72751 5.36189 1.89337 5.52668L6.00004 9.60668V14C5.99981 14.1133 6.02846 14.2248 6.08329 14.3239C6.13811 14.4231 6.2173 14.5066 6.31337 14.5667C6.41947 14.6324 6.54189 14.6671 6.66671 14.6667C6.77107 14.6661 6.87383 14.6409 6.96671 14.5933L9.63337 13.26C9.74332 13.2046 9.83577 13.1198 9.90049 13.0151C9.96521 12.9104 9.99967 12.7898 10 12.6667V9.60668L14.08 5.52668C14.2459 5.36189 14.3793 5.1674 14.4734 4.95334V4.91334C14.5926 4.66296 14.6584 4.39053 14.6667 4.11334V3.33334C14.6667 2.80291 14.456 2.2942 14.0809 1.91913C13.7058 1.54406 13.1971 1.33334 12.6667 1.33334ZM8.86004 8.86001C8.79825 8.9223 8.74937 8.99618 8.71619 9.07741C8.68302 9.15863 8.6662 9.24561 8.66671 9.33334V12.2533L7.33337 12.92V9.33334C7.33388 9.24561 7.31706 9.15863 7.28389 9.07741C7.25071 8.99618 7.20183 8.9223 7.14004 8.86001L3.60671 5.33334H12.3934L8.86004 8.86001ZM13.3334 4.00001H2.66671V3.33334C2.66671 3.15653 2.73695 2.98696 2.86197 2.86194C2.98699 2.73691 3.15656 2.66668 3.33337 2.66668H12.6667C12.8435 2.66668 13.0131 2.73691 13.1381 2.86194C13.2631 2.98696 13.3334 3.15653 13.3334 3.33334V4.00001Z" fill="#D4AF37"></path>
                    </svg>
                                    <span class="font-['Manrope:SemiBold',_sans-serif] font-semibold leading-[0] relative shrink-0 text-[#D4AF37] text-[12px] text-nowrap tracking-[-0.24px]">Filtros</span>
                </div>
                                <div class="flex h-[10px] items-center justify-center relative shrink-0 w-[20px]">
                                    <div class="flex-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down h-5 w-2.5 text-[#D4AF37]">
                                            <path d="m6 9 6 6 6-6"></path>
                    </svg>
                </div>
                </div>
            </div>
                        </button>
        </div>
    </div>

                <!-- Filtros Expandidos (oculto por padrão) -->
                <div id="filtersPanel" class="hidden w-full mt-4 pt-4 border-t border-[#2d2d2d]">
                    <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                        <input type="hidden" name="date_to" value="{{ request('date_to') }}">
            
            <div>
                            <label class="block text-xs font-medium text-[#AAAAAA] mb-2">Status</label>
                            <select name="status" class="w-full px-3 py-2 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#D4AF37]">
                    <option value="">Todos</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Pago</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expirado</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Falhou</option>
                    <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Estornado</option>
                </select>
            </div>
            
            <div>
                            <label class="block text-xs font-medium text-[#AAAAAA] mb-2">Método de Pagamento</label>
                            <select name="payment_method" class="w-full px-3 py-2 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#D4AF37]">
                    <option value="">Todos</option>
                    <option value="pix" {{ request('payment_method') == 'pix' ? 'selected' : '' }}>PIX</option>
                    <option value="credit_card" {{ request('payment_method') == 'credit_card' ? 'selected' : '' }}>Cartão de Crédito</option>
                    <option value="bank_slip" {{ request('payment_method') == 'bank_slip' ? 'selected' : '' }}>Boleto</option>
                </select>
            </div>
            
                        <div class="flex items-end">
                            <button type="submit" class="w-full px-4 py-2 bg-[#D4AF37] hover:bg-[#D4AF37] text-white rounded-lg text-sm font-medium transition-colors">
                    Aplicar Filtros
                </button>
            </div>
        </form>
                </div>
            </div>
            
            <!-- Tabela Desktop -->
            <div class="hidden md:block w-full overflow-x-auto">
                <div class="min-w-[1000px]">
                    <div class="w-full px-4 py-5">
                        <div class="flex flex-row gap-6 items-center">
                            <div class="w-[220px]">
                                <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Transação</span>
                            </div>
                            <div class="w-[110px]">
                                <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Origem</span>
                            </div>
                            <div class="w-[150px]">
                                <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Produto</span>
                            </div>
                            <div class="w-[220px]">
                                <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Cliente</span>
                            </div>
                            <div class="w-[150px]">
                                <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Pagamento</span>
                            </div>
                            <div class="w-[140px]">
                                <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Valor total</span>
                            </div>
                            <div class="w-[150px]">
                                <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Recebimento</span>
                            </div>
                            <div class="w-[120px]">
                                <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Hora</span>
                            </div>
                        </div>
                    </div>
                    <div class="w-full h-px mb-5 px-4">
                        <div class="h-px w-full bg-[#1f1f1f]"></div>
                    </div>
                    <div class="w-full flex flex-col gap-3">
                        <div class="flex flex-col gap-3">
                            @forelse($transactions as $transaction)
                                <div class="p-4 rounded-lg flex items-center justify-between cursor-pointer bg-[#1f1f1f] hover:bg-[#2a2a2a]" onclick="window.location.href='{{ route('transactions.show', $transaction->transaction_id) }}'">
                                    @php
                                        $metadata = is_array($transaction->metadata) ? $transaction->metadata : (is_string($transaction->metadata) ? json_decode($transaction->metadata, true) : []);
                                        $originKey = strtolower($metadata['created_via'] ?? $metadata['transaction_origin'] ?? 'api');
                                        $originLabel = str_contains($originKey, 'checkout') ? 'Checkout' : 'API';
                                        $originColor = $originLabel === 'Checkout' ? '#f97316' : '#D4AF37';

                                        $products = $transaction->products;
                                        if (!is_array($products)) {
                                            $products = [];
                                        }
                                        $primaryProduct = $products[0]['title'] ?? $products[0]['name'] ?? $metadata['sale_name'] ?? $transaction->sale_name ?? $transaction->description ?? 'Produto';
                                        $extraProducts = max(count($products) - 1, 0);

                                                $statusConfig = [
                                                    'pending' => ['label' => 'Pendente', 'color' => '#ffa782'],
                                                    'paid' => ['label' => 'Pago', 'color' => '#10b981'],
                                                    'cancelled' => ['label' => 'Cancelado', 'color' => '#6b7280'],
                                                    'expired' => ['label' => 'Expirado', 'color' => '#ef4444'],
                                                    'failed' => ['label' => 'Falhou', 'color' => '#ef4444'],
                                                    'refunded' => ['label' => 'Estornado', 'color' => '#8b5cf6'],
                                            'partially_refunded' => ['label' => 'Est. Parcial', 'color' => '#8b5cf6'],
                                                    'chargeback' => ['label' => 'Chargeback', 'color' => '#f59e0b'],
                                                ];
                                                $status = $transaction->is_retained ? 'pending' : $transaction->status;
                                                $config = $statusConfig[$status] ?? ['label' => ucfirst($status), 'color' => '#6b7280'];

                                        $paymentMap = [
                                            'pix' => ['label' => 'PIX', 'image' => 'https://logospng.org/download/pix/logo-pix-icone-512.png'],
                                            'credit_card' => ['label' => 'Cartão de Crédito', 'image' => asset('images/logo.png')],
                                            'bank_slip' => ['label' => 'Boleto', 'image' => asset('images/brpix.png')],
                                        ];
                                        $paymentVisual = $paymentMap[$transaction->payment_method] ?? ['label' => strtoupper(str_replace('_',' ', $transaction->payment_method)), 'image' => asset('images/logo.png')];
                                        $transactionTitle = $metadata['sale_name'] ?? $transaction->sale_name ?? $primaryProduct;
                                        $netAmount = $transaction->net_amount ?? $transaction->amount;
                                        $receivedAt = $transaction->paid_at ? $transaction->paid_at->format('d/m/Y H:i') : null;
                                            @endphp

                                    <div class="flex flex-row gap-6 items-center w-full">
                                        <!-- Transação -->
                                        <div class="w-[220px]">
                                            <div class="flex items-center gap-2">
                                                <p class="text-[11px] font-semibold tracking-[-0.24px]" style="color: #D4AF37;">ID: {{ $transaction->transaction_id }}</p>
                                                <button type="button" onclick="event.stopPropagation(); copyTransactionId('{{ $transaction->transaction_id }}')" class="inline-flex items-center justify-center hover:opacity-70 transition-opacity" title="Copiar ID">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy h-3.5 w-3.5" style="color: #D4AF37;">
                                                        <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                                        <path d="M4 16c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2h8c1.1 0 2 .9 2 2"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Origem -->
                                        <div class="w-[110px]">
                                            <p class="text-xs font-semibold tracking-[-0.24px] text-white">{{ $originLabel === 'Checkout' ? 'Checkout API' : 'API Pública' }}</p>
    </div>

                                        <!-- Produto -->
                                        <div class="w-[150px]">
                                            <p class="text-xs font-semibold tracking-[-0.24px] text-white truncate" title="{{ $transactionTitle }}">{{ \Illuminate\Support\Str::limit($transactionTitle, 30) }}</p>
                                        </div>

                                        <!-- Cliente -->
                                        <div class="w-[220px]">
                                            <p class="text-xs font-semibold tracking-[-0.24px] leading-[1.3] text-white">{{ $transaction->customer_data['name'] ?? 'N/A' }}</p>
                                            <p class="text-[11px] font-semibold tracking-[-0.24px] leading-[1.3] text-[#707070]">{{ $transaction->customer_data['email'] ?? 'N/A' }}</p>
                                            @if(!empty($transaction->customer_data['phone']))
                                                <p class="text-[11px] font-semibold tracking-[-0.24px] leading-[1.3] text-[#707070]">{{ $transaction->customer_data['phone'] }}</p>
                                            @endif
    </div>

                                        <!-- Pagamento -->
                                        <div class="w-[150px]">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-[#0d0d0d] flex items-center justify-center border border-[#1f1f1f]">
                                                    <img src="{{ $paymentVisual['image'] }}" alt="{{ $paymentVisual['label'] }}" class="h-6 w-6 object-contain">
                                                </div>
                                                <div>
                                                    <p class="text-xs font-semibold tracking-[-0.24px] text-white">{{ $paymentVisual['label'] }}</p>
                                                    <p class="text-[11px] font-semibold tracking-[-0.24px]" style="color: {{ $config['color'] }}">{{ $config['label'] }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Valor total -->
                                        <div class="w-[140px]">
                                            <p class="text-xs font-semibold tracking-[-0.24px] text-white">R$ {{ number_format($transaction->amount, 2, ',', '.') }}</p>
                                        </div>

                                        <!-- Recebimento -->
                                        <div class="w-[150px]">
                                            <p class="text-xs font-semibold tracking-[-0.24px] text-white">R$ {{ number_format($netAmount, 2, ',', '.') }}</p>
                                            <p class="text-[11px] font-semibold tracking-[-0.24px] {{ $receivedAt ? 'text-[#10b981]' : 'text-[#707070]' }}">
                                                {{ $receivedAt ?? 'Pendente' }}
                                            </p>
    </div>

                                        <!-- Hora -->
                                        <div class="w-[120px]">
                                            <p class="text-xs font-semibold tracking-[-0.24px] text-white">{{ $transaction->created_at->format('d/m/Y') }}</p>
                                            <p class="text-[11px] font-semibold tracking-[-0.24px] text-[#707070]">{{ $transaction->created_at->format('H:i:s') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center">
                                    <p class="text-[#AAAAAA] text-sm">Nenhuma transação encontrada</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
        </div>
        
            <!-- Cards Móveis -->
            <div class="md:hidden w-full flex flex-col gap-3 items-start justify-start overflow-clip relative shrink-0">
                <div class="flex flex-col gap-3 items-start justify-start relative shrink-0 w-full">
                    @forelse($transactions as $transaction)
                        @php
                            $metadata = is_array($transaction->metadata) ? $transaction->metadata : (is_string($transaction->metadata) ? json_decode($transaction->metadata, true) : []);
                            $originKey = strtolower($metadata['created_via'] ?? $metadata['transaction_origin'] ?? 'api');
                            $originLabel = str_contains($originKey, 'checkout') ? 'Checkout' : 'API';
                            $originColor = $originLabel === 'Checkout' ? '#f97316' : '#D4AF37';
                            $products = $transaction->products;
                            if (!is_array($products)) {
                                $products = [];
                            }
                            $primaryProduct = $products[0]['title'] ?? $products[0]['name'] ?? $metadata['sale_name'] ?? $transaction->sale_name ?? $transaction->description ?? 'Produto';
                            $extraProducts = max(count($products) - 1, 0);
                                    $statusConfig = [
                                        'pending' => ['label' => 'Pendente', 'color' => '#ffa782'],
                                        'paid' => ['label' => 'Pago', 'color' => '#10b981'],
                                        'cancelled' => ['label' => 'Cancelado', 'color' => '#6b7280'],
                                        'expired' => ['label' => 'Expirado', 'color' => '#ef4444'],
                                        'failed' => ['label' => 'Falhou', 'color' => '#ef4444'],
                                        'refunded' => ['label' => 'Estornado', 'color' => '#8b5cf6'],
                                    ];
                                    $status = $transaction->is_retained ? 'pending' : $transaction->status;
                                    $config = $statusConfig[$status] ?? ['label' => ucfirst($status), 'color' => '#6b7280'];
                            $paymentMap = [
                                'pix' => ['label' => 'PIX', 'image' => 'https://logospng.org/download/pix/logo-pix-icone-512.png'],
                                'credit_card' => ['label' => 'Cartão de Crédito', 'image' => asset('images/logo.png')],
                                'bank_slip' => ['label' => 'Boleto', 'image' => asset('images/brpix.png')],
                            ];
                            $paymentVisual = $paymentMap[$transaction->payment_method] ?? ['label' => strtoupper(str_replace('_',' ', $transaction->payment_method)), 'image' => asset('images/logo.png')];
                            $transactionTitle = $metadata['sale_name'] ?? $transaction->sale_name ?? $primaryProduct;
                            $netAmount = $transaction->net_amount ?? $transaction->amount;
                            $receivedAt = $transaction->paid_at ? $transaction->paid_at->format('d/m/Y H:i') : null;
                                @endphp
                        <div class="box-border flex flex-col gap-3 p-4 rounded-lg w-full cursor-pointer bg-[#1f1f1f]" onclick="window.location.href='{{ route('transactions.show', $transaction->transaction_id) }}'">
                            <div class="flex items-start justify-between w-full">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-[11px] font-semibold" style="color: #D4AF37;">ID: {{ $transaction->transaction_id }}</p>
                                        <button type="button" onclick="event.stopPropagation(); copyTransactionId('{{ $transaction->transaction_id }}')" class="inline-flex items-center justify-center hover:opacity-70 transition-opacity" title="Copiar ID">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy h-3.5 w-3.5" style="color: #D4AF37;">
                                                <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                                <path d="M4 16c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2h8c1.1 0 2 .9 2 2"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="px-3 py-1 rounded-full bg-[#161616]">
                                    <span class="text-[11px] font-semibold" style="color: {{ $config['color'] }}">{{ $config['label'] }}</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 w-full text-[11px] text-[#dddddd]">
                                <div>
                                    <p class="text-[#707070] uppercase tracking-[0.1em] text-[10px]">Origem</p>
                                    <span class="mt-1 block">{{ $originLabel === 'Checkout' ? 'Checkout API' : 'API Pública' }}</span>
                                </div>
                                <div>
                                    <p class="text-[#707070] uppercase tracking-[0.1em] text-[10px]">Valor total</p>
                                    <p class="mt-1 text-white font-semibold">R$ {{ number_format($transaction->amount, 2, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-[#707070] uppercase tracking-[0.1em] text-[10px]">Produto</p>
                                    <p class="mt-1 text-white font-semibold">{{ \Illuminate\Support\Str::limit($transactionTitle, 28) }}</p>
                                </div>
                                <div>
                                    <p class="text-[#707070] uppercase tracking-[0.1em] text-[10px]">Cliente</p>
                                    <p class="mt-1 text-white font-semibold">{{ $transaction->customer_data['name'] ?? 'N/A' }}</p>
                                    <p class="text-[#707070]">{{ $transaction->customer_data['email'] ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[#707070] uppercase tracking-[0.1em] text-[10px]">Pagamento</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="w-8 h-8 rounded-full bg-[#0d0d0d] flex items-center justify-center border border-[#1f1f1f]">
                                            <img src="{{ $paymentVisual['image'] }}" alt="{{ $paymentVisual['label'] }}" class="h-5 w-5 object-contain">
                                        </div>
                                        <div>
                                            <p class="text-white font-semibold leading-4">{{ $paymentVisual['label'] }}</p>
                                            <p class="text-[11px]" style="color: {{ $config['color'] }}">{{ $config['label'] }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[#707070] uppercase tracking-[0.1em] text-[10px]">Recebimento</p>
                                    <p class="mt-1 text-white font-semibold">R$ {{ number_format($netAmount, 2, ',', '.') }}</p>
                                    <p class="{{ $receivedAt ? 'text-[#10b981]' : 'text-[#707070]' }}">{{ $receivedAt ?? 'Pendente' }}</p>
                                    </div>
                                <div>
                                    <p class="text-[#707070] uppercase tracking-[0.1em] text-[10px]">Hora</p>
                                    <p class="mt-1 text-white font-semibold">{{ $transaction->created_at->format('d/m/Y') }}</p>
                                    <p class="text-[#707070]">{{ $transaction->created_at->format('H:i:s') }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center w-full">
                            <p class="text-[#AAAAAA] text-sm">Nenhuma transação encontrada</p>
                                </div>
                    @endforelse
                </div>
            </div>

            <!-- Paginação -->
            @if($transactions->hasPages())
                <div class="flex items-center justify-center w-full">
                    <div class="flex items-center gap-2">
                        @if($transactions->onFirstPage())
                            <button class="justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] hover:text-white button-custom" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left h-3 w-3">
                                    <path d="m15 18-6-6 6-6"></path>
                                </svg>
                                <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Anterior</span>
                            </button>
                        @else
                            <a href="{{ $transactions->appends(request()->query())->previousPageUrl() }}" class="justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] hover:text-white button-custom">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left h-3 w-3">
                                    <path d="m15 18-6-6 6-6"></path>
                                </svg>
                                <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Anterior</span>
                            </a>
                        @endif
                        
                        <div class="flex items-center gap-1">
                            @php
                                $currentPage = $transactions->currentPage();
                                $lastPage = $transactions->lastPage();
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($lastPage, $currentPage + 2);
                            @endphp
                            
                            @if($startPage > 1)
                                <a href="{{ $transactions->appends(request()->query())->url(1) }}" class="w-8 h-8 bg-[#1f1f1f] hover:bg-[#2a2a2a] rounded flex items-center justify-center">
                                    <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] text-white">1</span>
                                </a>
                                @if($startPage > 2)
                                    <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] text-[#aaaaaa]">...</span>
                                @endif
                            @endif
                            
                            @for($page = $startPage; $page <= $endPage; $page++)
                                @if($page == $currentPage)
                                    <div class="w-8 h-8 bg-[#D4AF37] rounded flex items-center justify-center">
                                        <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] text-white">{{ $page }}</span>
                </div>
                                @else
                                    <a href="{{ $transactions->appends(request()->query())->url($page) }}" class="w-8 h-8 bg-[#1f1f1f] hover:bg-[#2a2a2a] rounded flex items-center justify-center">
                                        <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] text-white">{{ $page }}</span>
                                    </a>
                                @endif
                            @endfor
                            
                            @if($endPage < $lastPage)
                                @if($endPage < $lastPage - 1)
                                    <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] text-[#aaaaaa]">...</span>
                                @endif
                                <a href="{{ $transactions->appends(request()->query())->url($lastPage) }}" class="w-8 h-8 bg-[#1f1f1f] hover:bg-[#2a2a2a] rounded flex items-center justify-center">
                                    <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] text-white">{{ $lastPage }}</span>
                                </a>
                            @endif
            </div>

                        @if($transactions->hasMorePages())
                            <a href="{{ $transactions->appends(request()->query())->nextPageUrl() }}" class="justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] hover:text-white button-custom">
                                <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Próximo</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right h-3 w-3">
                                    <path d="m9 18 6-6-6-6"></path>
                                </svg>
                            </a>
                        @else
                            <button class="justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] hover:text-white button-custom" disabled>
                                <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Próximo</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right h-3 w-3">
                                    <path d="m9 18 6-6-6-6"></path>
                        </svg>
                    </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleFilters() {
    const panel = document.getElementById('filtersPanel');
    if (panel) {
        panel.classList.toggle('hidden');
    }
}

function copyTransactionId(transactionId) {
    navigator.clipboard.writeText(transactionId).then(function() {
        // Feedback visual - muda o ícone para check
        const buttons = document.querySelectorAll(`button[onclick*="${transactionId}"]`);
        buttons.forEach(button => {
            const originalHTML = button.innerHTML;
            button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check h-3.5 w-3.5" style="color: #D4AF37;"><path d="M20 6 9 17l-5-5"></path></svg>';
            setTimeout(() => {
                button.innerHTML = originalHTML;
            }, 1000);
        });
    }).catch(function(err) {
        console.error('Erro ao copiar:', err);
    });
}
</script>
@endpush
@endsection
