// Dados de vendas por localização (latitude, longitude, intensidade, país)
const salesData = [
    { lat: -23.5505, lng: -46.6333, value: 85, country: "Brasil", sales: 450000 },
    { lat: 40.7128, lng: -74.0060, value: 92, country: "EUA", sales: 890000 },
    { lat: 51.5074, lng: -0.1278, value: 78, country: "Inglaterra", sales: 340000 },
    { lat: 48.8566, lng: 2.3522, value: 72, country: "França", sales: 280000 },
    { lat: 52.5200, lng: 13.4050, value: 68, country: "Alemanha", sales: 250000 },
    { lat: 41.9028, lng: 12.4964, value: 65, country: "Itália", sales: 220000 },
    { lat: 32.0853, lng: 34.7818, value: 55, country: "Israel", sales: 180000 },
    { lat: 35.6762, lng: 139.6503, value: 88, country: "Japão", sales: 750000 },
    { lat: 22.3193, lng: 114.1694, value: 82, country: "Hong Kong", sales: 620000 },
    { lat: 1.3521, lng: 103.8198, value: 75, country: "Singapura", sales: 310000 },
    { lat: -33.8688, lng: 151.2093, value: 70, country: "Austrália", sales: 290000 },
    { lat: 31.2304, lng: 30.0444, value: 48, country: "Egito", sales: 95000 },
    { lat: -34.6037, lng: -58.3816, value: 62, country: "Argentina", sales: 198000 },
    { lat: 19.4326, lng: -99.1332, value: 66, country: "México", sales: 240000 },
    { lat: 55.7558, lng: 37.6173, value: 58, country: "Rússia", sales: 170000 },
    { lat: 28.6139, lng: 77.2090, value: 64, country: "Índia", sales: 215000 },
    { lat: 39.9042, lng: 116.4074, value: 85, country: "China", sales: 580000 },
    { lat: 37.7749, lng: -122.4194, value: 90, country: "EUA (SF)", sales: 820000 },
];

let globe;

// Inicializar o globo
function initGlobe() {
    try {
        // Criar instância do globo
        globe = Globe()
            .globeImageUrl('./img/earth-dark.jpg')
            .bumpImageUrl('./img/earth-topology.png')
            .backgroundImageUrl('./img/night-sky.png')
            .pointsData(salesData)
            .pointAltitude('value')
            .pointColor(d => {
                const intensity = d.value;
                if (intensity >= 85) return '#ef4444'; // Vermelho - muito alto
                if (intensity >= 75) return '#f97316'; // Laranja - alto
                if (intensity >= 60) return '#fbbf24'; // Amarelo - moderado
                return '#10b981'; // Verde - baixo
            })
            .pointRadius(d => (d.value / 100) * 0.8)
            .pointsMerge(false)
            .pointLabel(d => `
                <div style="background: #1a1a1a; padding: 12px; border-radius: 8px; border: 1px solid #333;">
                    <div style="font-weight: 600; margin-bottom: 8px;">${d.country}</div>
                    <div style="font-size: 12px; color: #fbbf24;">Vendas: R$ ${(d.sales / 1000).toFixed(0)}k</div>
                    <div style="font-size: 12px; color: #707070; margin-top: 4px;">Intensidade: ${d.value}%</div>
                </div>
            `)
            .onPointHover(hoverD => {
                if (hoverD) {
                    document.body.style.cursor = 'pointer';
                } else {
                    document.body.style.cursor = 'default';
                }
            })
            .rotationSpeed(0.5);

        // Configurar o container
        const container = document.getElementById('globeViz');
        if (!container) {
            showError('Elemento #globeViz não encontrado no HTML');
            return;
        }

        globe(container);

        // Configurar câmera
        globe.scene().camera.positionZ = 300;

        // Auto-rotação
        globe.controls().autoRotate = true;
        globe.controls().autoRotateSpeed = 2;

        // Limpar mensagem de carregamento
        const loading = document.querySelector('.loading');
        if (loading) loading.remove();

        // Atualizar estatísticas
        updateStats();

        // Evento de clique para pausar/retomar rotação
        container.addEventListener('click', (event) => {
            globe.controls().autoRotate = !globe.controls().autoRotate;
        });

    } catch (error) {
        showError('Erro ao inicializar o globo: ' + error.message);
        console.error('Erro completo:', error);
    }
}

// Mostrar mensagem de erro
function showError(message) {
    const container = document.getElementById('globeViz');
    if (container) {
        container.innerHTML = `<div class="error-message">${message}</div>`;
    }
}

// Calcular estatísticas
function updateStats() {
    const totalSales = salesData.reduce((sum, item) => sum + item.sales, 0);
    const totalTransactions = salesData.length * Math.floor(Math.random() * 100 + 50);
    const avgTicket = totalSales / totalTransactions;
    const activeCountries = [...new Set(salesData.map(d => d.country))].length;

    const totalSalesEl = document.getElementById('total-sales');
    const activeCountriesEl = document.getElementById('active-countries');
    const totalTransactionsEl = document.getElementById('total-transactions');
    const avgTicketEl = document.getElementById('avg-ticket');

    if (totalSalesEl) {
        totalSalesEl.textContent = 'R$ ' + (totalSales / 1000000).toFixed(2).replace('.', ',') + 'M';
    }
    if (activeCountriesEl) {
        activeCountriesEl.textContent = activeCountries;
    }
    if (totalTransactionsEl) {
        totalTransactionsEl.textContent = totalTransactions.toLocaleString('pt-BR');
    }
    if (avgTicketEl) {
        avgTicketEl.textContent = 'R$ ' + (avgTicket / 1000).toFixed(0).replace('.', ',') + 'k';
    }
}

// Responsividade
window.addEventListener('resize', () => {
    const container = document.getElementById('globeViz');
    if (container && globe) {
        // Redimensionar globo se necessário
        const width = container.clientWidth;
        const height = container.clientHeight;
        if (globe.renderer && globe.camera) {
            globe.renderer.setSize(width, height);
            globe.camera.aspect = width / height;
            globe.camera.updateProjectionMatrix();
        }
    }
});

// Inicializar quando o documento estiver pronto
document.addEventListener('DOMContentLoaded', initGlobe);

// Fallback caso o DOM já esteja pronto
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    initGlobe();
}
