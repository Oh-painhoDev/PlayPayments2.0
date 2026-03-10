@extends('layouts.admin')

@section('title', 'Logs do Sistema')
@section('page-title', 'Monitoramento do Sistema')
@section('page-description', 'Logs ao vivo, métricas da API e alertas de segurança')

@section('content')
<div class="p-6 space-y-8 max-w-[1600px] mx-auto">
    
    <!-- DDoS Alerts -->
    @if(count($ddosAlerts) > 0)
    <div class="space-y-4 animate-in fade-in slide-in-from-top duration-500">
        @foreach($ddosAlerts as $alert)
        <div class="relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-r {{ $alert['level'] === 'critical' ? 'from-red-500/10 to-transparent' : 'from-yellow-500/10 to-transparent' }}"></div>
            <div class="relative bg-[#1a1111]/80 backdrop-blur-xl border {{ $alert['level'] === 'critical' ? 'border-red-500/30' : 'border-yellow-500/30' }} rounded-2xl p-5 shadow-2xl">
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl {{ $alert['level'] === 'critical' ? 'bg-red-500/20 text-red-500' : 'bg-yellow-500/20 text-yellow-500' }} flex items-center justify-center mr-4 border border-current/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold {{ $alert['level'] === 'critical' ? 'text-red-400' : 'text-yellow-400' }} text-lg tracking-tight lowercase first-letter:uppercase">
                                {{ $alert['level'] === 'critical' ? 'alerta crítico de segurança' : 'atenção ao sistema' }}
                            </h4>
                            <span class="text-[10px] font-medium px-2 py-0.5 rounded-full {{ $alert['level'] === 'critical' ? 'bg-red-500/20 text-red-400 shadow-[0_0_10px_rgba(239,68,68,0.2)]' : 'bg-yellow-500/20 text-yellow-500' }}">
                                {{ $alert['level'] === 'critical' ? 'CRITICAL' : 'WARNING' }}
                            </span>
                        </div>
                        <p class="text-gray-300 text-sm mt-1 font-medium">{{ $alert['message'] }}</p>
                        <div class="flex items-center mt-3 space-x-4">
                            <span class="text-gray-500 text-[10px] flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $alert['timestamp'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- API Rate Limits Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @php
            $metricMeta = [
                ['id' => 'minute', 'label' => 'Requisições/Minuto', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                ['id' => 'hour', 'label' => 'Requisições/Hora', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['id' => 'day', 'label' => 'Requisições/Dia', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z']
            ];
        @endphp

        @foreach($metricMeta as $meta)
        @php
            $id = $meta['id'];
            $usage = $apiStats['usage'][$id];
            $value = $apiStats['current_'.$id];
            $limit = $apiStats['limits'][$id];
        @endphp
        <div class="group relative bg-[#121212] border border-[#222] rounded-2xl p-6 transition-all duration-300 hover:border-[#333] hover:shadow-[0_8px_30px_rgb(0,0,0,0.5)] overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-white/5 to-transparent rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110 duration-500"></div>
            
            <div class="relative flex items-center justify-between mb-4">
                <div class="p-2 bg-[#1a1a1a] rounded-xl border border-white/5 text-gray-400 group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['icon'] }}" /></svg>
                </div>
                <span id="metric-value-{{ $id }}" class="text-2xl font-black tracking-tight {{ $usage > 80 ? 'text-red-500' : ($usage > 60 ? 'text-yellow-500' : 'text-emerald-500') }}">
                    {{ number_format($value) }}
                </span>
            </div>
            
            <h3 class="relative text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">{{ $meta['label'] }}</h3>
            
            <div class="relative w-full bg-[#1a1a1a] rounded-full h-1.5 mb-2">
                <div id="metric-bar-{{ $id }}" class="h-1.5 rounded-full transition-all duration-1000 ease-out {{ $usage > 80 ? 'bg-gradient-to-r from-red-600 to-red-400 shadow-[0_0_8px_rgba(239,68,68,0.4)]' : ($usage > 60 ? 'bg-gradient-to-r from-yellow-600 to-yellow-400 shadow-[0_0_8px_rgba(245,158,11,0.4)]' : 'bg-gradient-to-r from-emerald-600 to-emerald-400 shadow-[0_0_8px_rgba(16,185,129,0.4)]') }}" 
                     style="width: {{ min($usage, 100) }}%"></div>
            </div>
            <div class="relative flex justify-between items-center text-[10px]">
                <span class="text-gray-600 font-medium">Capacidade: <span class="text-gray-400">{{ number_format($limit) }}</span></span>
                <span id="metric-pct-{{ $id }}" class="font-bold {{ $usage > 80 ? 'text-red-500' : ($usage > 60 ? 'text-yellow-500' : 'text-emerald-500') }}">{{ round($usage, 1) }}%</span>
            </div>
        </div>
        @endforeach
    </div>

    <!-- API Traffic Chart -->
    <div class="bg-[#121212] border border-[#222] rounded-2xl p-6 shadow-xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-white/[0.02] to-transparent pointer-events-none"></div>
        <div class="relative flex items-center justify-between mb-8">
            <div>
                <h3 class="text-lg font-bold text-white tracking-tight">Análise de Tráfego</h3>
                <p class="text-xs text-gray-500">Fluxo de requisições API na última hora</p>
            </div>
            <div class="flex items-center text-[10px] space-x-1">
                <span class="w-2 h-2 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.6)] animate-pulse"></span>
                <span class="text-gray-400 font-bold uppercase tracking-widest">Real-time stats</span>
            </div>
        </div>
        <div class="relative h-[280px]">
            <canvas id="trafficChart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Live Terminal Logs -->
        <div class="flex flex-col bg-[#0a0a0a] border border-[#222] rounded-2xl overflow-hidden shadow-2xl h-[550px]">
            <div class="bg-[#1a1a1a] px-5 py-4 border-b border-[#222] flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex space-x-1.5 mr-4">
                        <div class="w-3 h-3 rounded-full bg-[#ff5f56]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#ffbd2e]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#27c93f]"></div>
                    </div>
                    <h3 class="text-sm font-bold text-gray-300 tracking-tight flex items-center">
                        <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Laravel Terminal Output
                    </h3>
                </div>
                <div class="flex items-center space-x-3 text-[10px]">
                    <span class="flex items-center text-emerald-500 font-bold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                        Streaming
                    </span>
                </div>
            </div>
            <div id="liveLogs" class="flex-1 p-5 overflow-y-auto font-mono text-[11px] leading-relaxed scrollbar-thin scrollbar-thumb-[#333]">
                @foreach($logs as $log)
                <div class="group py-1 border-b border-white/[0.03] last:border-0 hover:bg-white/[0.02] transition-colors">
                    <div class="flex items-start">
                        <span class="text-gray-600 mr-3 select-none flex-shrink-0">[{{ $log['timestamp'] ?? now()->format('H:i:s') }}]</span>
                        <span class="font-bold mr-3 flex-shrink-0 {{ $log['is_error'] ? 'text-red-500' : ($log['level'] === 'WARNING' ? 'text-yellow-500' : 'text-blue-400') }}">
                            {{ $log['level'] }}
                        </span>
                        <span class="text-gray-300 break-all">{{ Str::limit($log['message'], 1000) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- System Errors Explorer -->
        <div class="flex flex-col bg-[#0a0a0a] border border-[#222] rounded-2xl overflow-hidden shadow-2xl h-[550px]">
            <div class="bg-[#1a1a1a] px-5 py-4 border-b border-[#222] flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-300 tracking-tight flex items-center uppercase tracking-widest">
                    <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Recent Internal Errors
                </h3>
                <span class="text-[10px] bg-red-500/10 text-red-500 font-black border border-red-500/20 px-2 py-0.5 rounded-md">
                    {{ count($errors) }} FOUND
                </span>
            </div>
            <div class="flex-1 overflow-y-auto p-5 space-y-4 scrollbar-thin scrollbar-thumb-[#333]">
                @forelse($errors as $error)
                <div class="relative overflow-hidden bg-[#161616] border border-red-500/20 rounded-xl p-4 transition-all duration-300 hover:border-red-500/40 group">
                    <div class="absolute inset-y-0 left-0 w-1 bg-red-500 group-hover:w-1.5 transition-all"></div>
                    <div class="flex items-start">
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] text-gray-600 font-mono flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ $error['timestamp'] }}
                                </span>
                            </div>
                            <p class="text-[12px] text-red-400 font-mono leading-relaxed line-clamp-3 group-hover:line-clamp-none transition-all duration-500 cursor-help">{{ $error['message'] }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center h-full text-center space-y-4 opacity-50">
                    <div class="w-20 h-20 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <p class="text-white font-bold tracking-tight">Sistema Nominal</p>
                        <p class="text-gray-500 text-xs mt-1 lowercase font-medium">nenhum erro detectado nas últimas horas</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Management Controls -->
    <div class="flex flex-wrap items-center justify-between gap-6 bg-[#121212] border border-[#222] rounded-2xl p-6 shadow-xl relative overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/[0.03] to-transparent pointer-events-none"></div>
        <div class="relative flex items-center gap-4">
            <button onclick="refreshLogs()" class="flex items-center px-6 py-2.5 bg-white text-black font-bold text-sm rounded-xl hover:bg-gray-200 transition-all active:scale-95 shadow-lg shadow-white/5">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                FORCED REFRESH
            </button>
            <form action="{{ route('admin.system-logs.clear') }}" method="POST" onsubmit="return confirm('ATENÇÃO: Deseja realmente purgar todos os logs?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="flex items-center px-6 py-2.5 bg-red-600/10 text-red-500 border border-red-500/20 font-bold text-sm rounded-xl hover:bg-red-600 hover:text-white transition-all active:scale-95">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v2m4 0h-4" /></svg>
                    PURGE ALL LOGS
                </form>
        </div>
        <div class="relative flex flex-col items-end">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                <span class="text-xs font-bold text-gray-400 tracking-tighter uppercase">Syncing protocol active</span>
            </div>
            <p class="text-[10px] text-gray-600 font-medium">Delta auto-update: 5000ms</p>
        </div>
    </div>
</div>

<style>
    #liveLogs::-webkit-scrollbar { width: 4px; }
    #liveLogs::-webkit-scrollbar-track { background: transparent; }
    #liveLogs::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }
    #liveLogs::-webkit-scrollbar-thumb:hover { background: #444; }
    
    .scrollbar-thin::-webkit-scrollbar { width: 4px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: #222; border-radius: 10px; }
    .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #333; }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let lastCheck = {{ time() }};

// Enhanced Traffic Chart
const ctx = document.getElementById('trafficChart').getContext('2d');
const gradient = ctx.createLinearGradient(0, 0, 0, 240);
gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
gradient.addColorStop(1, 'rgba(0, 0, 0, 0)');

const trafficChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($apiStats['hourly_breakdown'], 'time')) !!},
        datasets: [{
            label: 'Requisições',
            data: {!! json_encode(array_column($apiStats['hourly_breakdown'], 'count')) !!},
            borderColor: '#3b82f6',
            borderWidth: 3,
            backgroundColor: gradient,
            tension: 0.45,
            fill: true,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#3b82f6',
            pointBorderWidth: 2,
            pointRadius: 0,
            pointHoverRadius: 6,
            pointHoverBackgroundColor: '#3b82f6',
            pointHoverBorderColor: '#fff',
            pointHoverBorderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index',
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#161616',
                titleColor: '#fff',
                bodyColor: '#aaa',
                borderColor: '#333',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 12,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return context.parsed.y + ' Requisições';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(255, 255, 255, 0.05)',
                    drawBorder: false
                },
                ticks: {
                    color: '#4b5563',
                    font: { size: 10, weight: 'bold' },
                    padding: 10,
                    callback: function(value) {
                        if (value >= 1000) return (value / 1000) + 'k';
                        return value;
                    }
                }
            },
            x: {
                grid: { display: false },
                ticks: {
                    color: '#4b5563',
                    font: { size: 10, weight: 'bold' },
                    maxRotation: 0,
                    padding: 10
                }
            }
        }
    }
});

// Live logs update
async function updateLiveLogs() {
    try {
        const response = await fetch(`{{ route('admin.system-logs.live') }}?last_check=${lastCheck}`);
        const data = await response.json();
        
        if (data.stats) {
            updateMetrics(data.stats);
            updateChart(data.stats.hourly_breakdown);
        }
        
        if (data.logs && data.logs.length > 0) {
            const logsContainer = document.getElementById('liveLogs');
            
            data.logs.forEach(log => {
                const logDiv = document.createElement('div');
                logDiv.className = 'group py-1 border-b border-white/[0.03] last:border-0 hover:bg-white/[0.02] transition-colors animate-in fade-in duration-500';
                
                let levelColor = 'text-blue-400';
                if (log.is_error) levelColor = 'text-red-500';
                else if (log.level === 'WARNING') levelColor = 'text-yellow-500';
                
                logDiv.innerHTML = `
                    <div class="flex items-start">
                        <span class="text-gray-600 mr-3 select-none flex-shrink-0">[${log.timestamp}]</span>
                        <span class="font-bold mr-3 flex-shrink-0 ${levelColor}">${log.level}</span>
                        <span class="text-gray-300 break-all">${log.message}</span>
                    </div>
                `;
                
                logsContainer.insertBefore(logDiv, logsContainer.firstChild);
            });
            
            while (logsContainer.children.length > 200) {
                logsContainer.removeChild(logsContainer.lastChild);
            }
        }
        lastCheck = data.timestamp;
    } catch (error) {
        console.error('Error updating logs:', error);
    }
}

function updateMetrics(stats) {
    ['minute', 'hour', 'day'].forEach(id => {
        const valElem = document.getElementById(`metric-value-${id}`);
        const barElem = document.getElementById(`metric-bar-${id}`);
        const pctElem = document.getElementById(`metric-pct-${id}`);
        
        if (valElem) valElem.innerText = new Intl.NumberFormat().format(stats[`current_${id}`]);
        if (pctElem) {
            pctElem.innerText = Math.round(stats.usage[id] * 10) / 10 + '%';
            
            // Update colors
            const usage = stats.usage[id];
            let colorClass = 'text-emerald-500';
            let barBg = 'bg-gradient-to-r from-emerald-600 to-emerald-400 shadow-[0_0_8px_rgba(16,185,129,0.4)]';
            
            if (usage > 80) {
                colorClass = 'text-red-500';
                barBg = 'bg-gradient-to-r from-red-600 to-red-400 shadow-[0_0_8px_rgba(239,68,68,0.4)]';
            } else if (usage > 60) {
                colorClass = 'text-yellow-500';
                barBg = 'bg-gradient-to-r from-yellow-600 to-yellow-400 shadow-[0_0_8px_rgba(245,158,11,0.4)]';
            }
            
            valElem.className = `text-2xl font-black tracking-tight ${colorClass}`;
            pctElem.className = `font-bold ${colorClass}`;
            barElem.className = `h-1.5 rounded-full transition-all duration-1000 ease-out ${barBg}`;
            barElem.style.width = Math.min(usage, 100) + '%';
        }
    });
}

function updateChart(hourlyBreakdown) {
    if (trafficChart) {
        trafficChart.data.labels = hourlyBreakdown.map(d => d.time);
        trafficChart.data.datasets[0].data = hourlyBreakdown.map(d => d.count);
        trafficChart.update('none'); // Update without animation for performance
    }
}

function refreshLogs() {
    const btn = event.currentTarget;
    btn.innerHTML = 'REFRESHING...';
    btn.classList.add('opacity-50', 'pointer-events-none');
    setTimeout(() => location.reload(), 500);
}

// Auto-refresh every 3 seconds for real-time feel
setInterval(updateLiveLogs, 3000);
</script>
@endpush
@endsection
