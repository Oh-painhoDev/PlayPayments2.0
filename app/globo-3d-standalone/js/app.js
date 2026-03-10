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
let globeInitialized = false;

function initGlobe() {
    try {
        const container = document.getElementById('globeViz');
        if (!container) return;

        if (typeof Globe === 'undefined' || typeof THREE === 'undefined') {
            console.error('Bibliotecas não carregadas');
            container.innerHTML = '<p style="color: red;">Erro: Bibliotecas não carregadas</p>';
            return;
        }

        globe = Globe()
            .globeImageUrl('./img/earth-dark.jpg')
            .bumpImageUrl('./img/earth-topology.png')
            .backgroundImageUrl('./img/night-sky.png')
            .pointsData(salesData)
            .pointLat('lat')
            .pointLng('lng')
            .pointColor(d => {
                const intensity = d.value;
                if (intensity >= 85) return '#ef4444';
                if (intensity >= 75) return '#f97316';
                if (intensity >= 60) return '#fbbf24';
                return '#10b981';
            })
            .pointAltitude(d => d.value / 250)
            .pointRadius(d => (d.value / 200) * 0.25)
            .pointLabel(d => `${d.country}\nVendas: R$ ${(d.sales / 1000).toFixed(0)}k`)
            .onPointHover(d => document.body.style.cursor = d ? 'pointer' : 'default');

        globe(container);
        globeInitialized = true;
        console.log('✅ Globo inicializado');

        setTimeout(() => {
            if (globe && globe.scene && globe.scene()) {
                const camera = globe.scene().camera;
                if (camera) {
                    camera.position.z = 300;
                }
            }
        }, 500);

    } catch (error) {
        console.error('❌ Erro ao inicializar globo:', error);
        const container = document.getElementById('globeViz');
        if (container) {
            container.innerHTML = `<p style="color: red;">Erro: ${error.message}</p>`;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(initGlobe, 100);
});
