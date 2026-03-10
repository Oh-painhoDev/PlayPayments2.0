@extends('layouts.admin')

@section('title', 'Logs do Sistema')
@section('page-title', 'Monitoramento do Sistema')
@section('page-description', 'Logs ao vivo, métricas da API e alertas de segurança')

@section('content')
<div class="p-6 space-y-6">
    
    <!-- DDoS Alerts -->
    @if(count($ddosAlerts) > 0)
    <div class="space-y-3">
        @foreach($ddosAlerts as $alert)
        <div class="bg-{{ $alert['level'] === 'critical' ? 'red' : 'yellow' }}-50 border border-{{ $alert['level'] === 'critical' ? 'red' : 'yellow' }}-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-{{ $alert['level'] === 'critical' ? 'red' : 'yellow' }}-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1">
                    <h4 class="font-semibold text-{{ $alert['level'] === 'critical' ? 'red' : 'yellow' }}-800 mb-1">
                        {{ $alert['level'] === 'critical' ? '🚨 ALERTA CRÍTICO' : '⚠️ ATENÇÃO' }}
                    </h4>
                    <p class="text-{{ $alert['level'] === 'critical' ? 'red' : 'yellow' }}-700 text-sm">{{ $alert['message'] }}</p>
                    <p class="text-{{ $alert['level'] === 'critical' ? 'red' : 'yellow' }}-600 text-xs mt-1">{{ $alert['timestamp'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- API Rate Limits -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-medium text-gray-700">Requisições/Minuto</h3>
                <span class="text-2xl font-bold {{ $apiStats['usage']['minute'] > 80 ? 'text-red-600' : ($apiStats['usage']['minute'] > 60 ? 'text-yellow-600' : 'text-green-600') }}">
                    {{ $apiStats['current_minute'] }}
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="h-2.5 rounded-full {{ $apiStats['usage']['minute'] > 80 ? 'bg-red-600' : ($apiStats['usage']['minute'] > 60 ? 'bg-yellow-500' : 'bg-green-600') }}" 
                     style="width: {{ min($apiStats['usage']['minute'], 100) }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Limite: {{ number_format($apiStats['limits']['minute']) }}</p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-medium text-gray-700">Requisições/Hora</h3>
                <span class="text-2xl font-bold {{ $apiStats['usage']['hour'] > 80 ? 'text-red-600' : ($apiStats['usage']['hour'] > 60 ? 'text-yellow-600' : 'text-green-600') }}">
                    {{ number_format($apiStats['current_hour']) }}
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="h-2.5 rounded-full {{ $apiStats['usage']['hour'] > 80 ? 'bg-red-600' : ($apiStats['usage']['hour'] > 60 ? 'bg-yellow-500' : 'bg-green-600') }}" 
                     style="width: {{ min($apiStats['usage']['hour'], 100) }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Limite: {{ number_format($apiStats['limits']['hour']) }}</p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-medium text-gray-700">Requisições/Dia</h3>
                <span class="text-2xl font-bold {{ $apiStats['usage']['day'] > 80 ? 'text-red-600' : ($apiStats['usage']['day'] > 60 ? 'text-yellow-600' : 'text-green-600') }}">
                    {{ number_format($apiStats['current_day']) }}
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="h-2.5 rounded-full {{ $apiStats['usage']['day'] > 80 ? 'bg-red-600' : ($apiStats['usage']['day'] > 60 ? 'bg-yellow-500' : 'bg-green-600') }}" 
                     style="width: {{ min($apiStats['usage']['day'], 100) }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Limite: {{ number_format($apiStats['limits']['day']) }}</p>
        </div>
    </div>

    <!-- API Traffic Chart -->
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Tráfego da Última Hora</h3>
        <canvas id="trafficChart" height="80"></canvas>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Live Logs -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">🔴 Logs ao Vivo</h3>
                <div class="flex items-center space-x-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    <span class="text-xs text-gray-600">Online</span>
                </div>
            </div>
            <div id="liveLogs" class="bg-gray-900 rounded-lg p-4 h-96 overflow-y-auto font-mono text-xs space-y-1">
                @foreach(array_slice($logs, 0, 50) as $log)
                <div class="log-entry {{ $log['is_error'] ? 'text-red-400' : 'text-green-400' }}">
                    <span class="text-gray-500">[{{ $log['timestamp'] }}]</span>
                    <span class="text-{{ $log['level'] === 'ERROR' ? 'red' : ($log['level'] === 'WARNING' ? 'yellow' : 'blue') }}-400">[{{ $log['level'] }}]</span>
                    <span>{{ Str::limit($log['message'], 150) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Errors -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">❌ Erros Recentes</h3>
                <span class="px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                    {{ count($errors) }}
                </span>
            </div>
            <div class="space-y-3 h-96 overflow-y-auto">
                @forelse($errors as $error)
                <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-4 h-4 text-red-600 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 mb-1">{{ $error['timestamp'] }}</p>
                            <p class="text-sm text-red-800 font-mono break-all">{{ Str::limit($error['message'], 200) }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>Nenhum erro encontrado! ✅</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex items-center space-x-4">
            <button onclick="refreshLogs()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                🔄 Atualizar
            </button>
            <form action="{{ route('admin.system-logs.clear') }}" method="POST" onsubmit="return confirm('Tem certeza que deseja limpar os logs?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                    🗑️ Limpar Logs
                </button>
            </form>
        </div>
        <p class="text-xs text-gray-500">Atualização automática a cada 5 segundos</p>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let lastCheck = {{ time() }};

// Traffic Chart
const ctx = document.getElementById('trafficChart').getContext('2d');
const trafficChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($apiStats['hourly_breakdown'], 'time')) !!},
        datasets: [{
            label: 'Requisições por Minuto',
            data: {!! json_encode(array_column($apiStats['hourly_breakdown'], 'count')) !!},
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Live logs update
async function updateLiveLogs() {
    try {
        const response = await fetch(`{{ route('admin.system-logs.live') }}?last_check=${lastCheck}`);
        const data = await response.json();
        
        if (data.logs.length > 0) {
            const logsContainer = document.getElementById('liveLogs');
            
            data.logs.forEach(log => {
                const logDiv = document.createElement('div');
                logDiv.className = `log-entry ${log.is_error ? 'text-red-400' : 'text-green-400'}`;
                
                const level = log.level;
                const levelColor = level === 'ERROR' ? 'red' : (level === 'WARNING' ? 'yellow' : 'blue');
                
                logDiv.innerHTML = `
                    <span class="text-gray-500">[${log.timestamp}]</span>
                    <span class="text-${levelColor}-400">[${log.level}]</span>
                    <span>${log.message.substring(0, 150)}</span>
                `;
                
                logsContainer.insertBefore(logDiv, logsContainer.firstChild);
            });
            
            // Keep only last 100 logs
            while (logsContainer.children.length > 100) {
                logsContainer.removeChild(logsContainer.lastChild);
            }
            
            lastCheck = data.timestamp;
        }
    } catch (error) {
        console.error('Error updating logs:', error);
    }
}

function refreshLogs() {
    location.reload();
}

// Auto-refresh every 5 seconds
setInterval(updateLiveLogs, 5000);
</script>
@endpush
@endsection
