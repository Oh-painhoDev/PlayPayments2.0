<!-- Exemplo de integração com Laravel -->

<!-- Opção 1: Simples (em uma view Blade) -->
<div style="margin: 20px 0;">
    <iframe 
        src="/globo-3d-standalone/" 
        style="
            width: 100%; 
            height: 600px; 
            border: none; 
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        "
        title="Globo 3D - Vendas por Localização">
    </iframe>
</div>

<!-- Opção 2: Com espaçamento e título -->
<div class="globo-container" style="margin: 40px 0;">
    <h2 style="margin-bottom: 20px; color: #333;">Visualização Global de Vendas</h2>
    <div style="background: #f5f5f5; padding: 20px; border-radius: 12px;">
        <iframe 
            src="/globo-3d-standalone/" 
            style="
                width: 100%; 
                height: 700px; 
                border: none; 
                border-radius: 8px;
            "
            title="Globo 3D - Vendas por Localização">
        </iframe>
    </div>
</div>

<!-- Opção 3: Com componente Laravel -->
<x-globo-3d />

<!-- Opção 4: Fullscreen modal -->
<script>
function abrirGlobo3D() {
    window.open('/globo-3d-standalone/', 'Globo3D', 'width=1200,height=800');
}
</script>

<button onclick="abrirGlobo3D()" class="btn btn-primary">
    🌍 Abrir Globo 3D em Tela Cheia
</button>
