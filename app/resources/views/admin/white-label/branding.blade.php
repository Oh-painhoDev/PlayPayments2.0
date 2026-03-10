@extends('layouts.admin')

@section('page-title', 'Personalização White Label')
@section('page-description', 'Configure favicon, cores e banners do sistema')

@section('content')
<div class="p-6">
    @if(isset($migration_warning) && $migration_warning)
        <div class="bg-yellow-900/20 border border-yellow-700 text-yellow-400 px-4 py-3 rounded-lg mb-4">
            <strong>Atenção:</strong> As tabelas necessárias não existem. Execute as migrations: <code class="bg-yellow-900/30 px-2 py-1 rounded">php artisan migrate</code>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-900/20 border border-green-700 text-green-400 px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-900/20 border border-red-700 text-red-400 px-4 py-3 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800 bg-[#0f0f0f]">
            <h2 class="text-xl font-semibold text-white">Configurações de Personalização</h2>
            <p class="text-sm text-[#6B7280] mt-1">Personalize a aparência do sistema</p>
        </div>

        <form action="{{ route('admin.white-label.branding.update') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf

            <!-- Cor Principal -->
            <div class="mb-6">
                <label for="primary_color" class="block text-sm font-medium text-white mb-2">
                    Cor Principal
                </label>
                <div class="flex items-center gap-4">
                    <input 
                        type="color" 
                        id="primary_color" 
                        name="primary_color" 
                        value="{{ $primary_color }}" 
                        class="h-10 w-20 rounded border border-gray-700 bg-[#1a1a1a] cursor-pointer"
                    >
                    <input 
                        type="text" 
                        value="{{ $primary_color }}" 
                        class="flex-1 px-3 py-2 border border-gray-700 bg-[#1a1a1a] text-white rounded-lg focus:ring-2 focus:ring-[#D4AF37] focus:border-[#D4AF37]"
                        readonly
                        id="primary_color_text"
                    >
                </div>
                <p class="text-xs text-[#6B7280] mt-1">Cor utilizada em botões, links e elementos de destaque</p>
            </div>

            <!-- Favicon -->
            <div class="mb-6">
                <label for="favicon" class="block text-sm font-medium text-white mb-2">
                    Favicon
                </label>
                @if($favicon)
                    <div class="mb-3">
                        <img src="{{ $favicon }}" alt="Favicon atual" class="h-16 w-16 border border-gray-700 rounded" onerror="this.style.display='none'">
                        <p class="text-xs text-[#6B7280] mt-1">Favicon atual ({{ $favicon_type === 'url' ? 'URL' : 'Arquivo' }})</p>
                    </div>
                @endif
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-[#6B7280] mb-1">Enviar arquivo:</label>
                        <input 
                            type="file" 
                            id="favicon_file" 
                            name="favicon_file" 
                            accept=".ico,.png,.svg,.webp"
                            class="block w-full text-sm text-[#6B7280] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#D4AF37]/20 file:text-[#D4AF37] hover:file:bg-[#D4AF37]/30"
                        >
                    </div>
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-[#1a1a1a] text-[#6B7280]">OU</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#6B7280] mb-1">Usar URL:</label>
                        <input 
                            type="url" 
                            id="favicon_url" 
                            name="favicon_url" 
                            value="{{ $favicon && $favicon_type === 'url' ? $favicon : '' }}"
                            placeholder="https://exemplo.com/favicon.ico"
                            class="block w-full px-3 py-2 border border-gray-700 bg-[#1a1a1a] text-white rounded-lg focus:ring-2 focus:ring-[#D4AF37] focus:border-[#D4AF37] text-sm"
                        >
                    </div>
                </div>
                <p class="text-xs text-[#6B7280] mt-1">Formato: ICO, PNG, SVG ou WebP. Tamanho máximo: 2MB (arquivo) ou URL válida</p>
            </div>

            <!-- Logo -->
            <div class="mb-6">
                <label for="logo" class="block text-sm font-medium text-white mb-2">
                    Logo
                </label>
                @if($logo)
                    <div class="mb-3">
                        <img src="{{ $logo }}" alt="Logo atual" class="h-[27px] w-auto border border-gray-700 rounded" style="max-width: 110px;" onerror="this.style.display='none'">
                        <p class="text-xs text-[#6B7280] mt-1">Logo atual ({{ $logo_type === 'url' ? 'URL' : 'Arquivo' }})</p>
                    </div>
                @endif
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-[#6B7280] mb-1">Enviar arquivo:</label>
                        <input 
                            type="file" 
                            id="logo_file" 
                            name="logo_file" 
                            accept="image/jpeg,image/jpg,image/png,image/webp,image/svg+xml"
                            class="block w-full text-sm text-[#6B7280] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#D4AF37]/20 file:text-[#D4AF37] hover:file:bg-[#D4AF37]/30"
                        >
                    </div>
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-[#1a1a1a] text-[#6B7280]">OU</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#6B7280] mb-1">Usar URL:</label>
                        <input 
                            type="url" 
                            id="logo_url" 
                            name="logo_url" 
                            value="{{ $logo && $logo_type === 'url' ? $logo : '' }}"
                            placeholder="https://exemplo.com/logo.png"
                            class="block w-full px-3 py-2 border border-gray-700 bg-[#1a1a1a] text-white rounded-lg focus:ring-2 focus:ring-[#D4AF37] focus:border-[#D4AF37] text-sm"
                        >
                    </div>
                </div>
                <p class="text-xs text-[#6B7280] mt-1">Formato: JPEG, JPG, PNG, WebP ou SVG. Tamanho máximo: 2MB (arquivo) ou URL válida. <strong class="text-white">Medida recomendada: 110x27px (proporção 4:1)</strong></p>
            </div>

            <!-- Banner do Dashboard -->
            <div class="mb-6">
                <label for="dashboard_banner" class="block text-sm font-medium text-white mb-2">
                    Banner do Dashboard
                </label>
                @if($dashboard_banner)
                    <div class="mb-3">
                        <img src="{{ $dashboard_banner }}" alt="Banner atual" class="max-w-full h-auto border border-gray-700 rounded-lg" style="max-height: 200px;" onerror="this.style.display='none'">
                        <p class="text-xs text-[#6B7280] mt-1">Banner atual ({{ $dashboard_banner_type === 'url' ? 'URL' : 'Arquivo' }})</p>
                    </div>
                @endif
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-[#6B7280] mb-1">Enviar arquivo:</label>
                        <input 
                            type="file" 
                            id="dashboard_banner_file" 
                            name="dashboard_banner_file" 
                            accept="image/jpeg,image/jpg,image/png,image/webp"
                            class="block w-full text-sm text-[#6B7280] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#D4AF37]/20 file:text-[#D4AF37] hover:file:bg-[#D4AF37]/30"
                        >
                    </div>
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-[#1a1a1a] text-[#6B7280]">OU</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#6B7280] mb-1">Usar URL:</label>
                        <input 
                            type="url" 
                            id="dashboard_banner_url" 
                            name="dashboard_banner_url" 
                            value="{{ $dashboard_banner && $dashboard_banner_type === 'url' ? $dashboard_banner : '' }}"
                            placeholder="https://exemplo.com/banner.jpg"
                            class="block w-full px-3 py-2 border border-gray-700 bg-[#1a1a1a] text-white rounded-lg focus:ring-2 focus:ring-[#D4AF37] focus:border-[#D4AF37] text-sm"
                        >
                    </div>
                </div>
                <p class="text-xs text-[#6B7280] mt-1">Formato: JPEG, JPG, PNG ou WebP. Tamanho máximo: 5MB (arquivo) ou URL válida. Medida recomendada: 1440x400px.</p>
            </div>

            <!-- Banner das Telas de Autenticação -->
            <div class="mb-8 border border-gray-800 rounded-lg p-5 bg-[#111111]">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <label for="auth_banner" class="block text-sm font-medium text-white">
                            Banner das telas de Login/Cadastro
                        </label>
                        <p class="text-xs text-[#6B7280] mt-1">Escolha uma imagem promocional para aplicar nas telas públicas de autenticação.</p>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-[#e5e7eb]">
                        <input type="hidden" name="auth_banner_active" value="0">
                        <input 
                            type="checkbox" 
                            name="auth_banner_active" 
                            value="1" 
                            class="form-checkbox h-4 w-4 text-[#D4AF37] rounded border-gray-600 bg-transparent focus:ring-[#D4AF37]"
                            {{ $auth_banner_active ? 'checked' : '' }}
                        >
                        <span>Ativar banner</span>
                    </label>
                </div>

                @if($auth_banner)
                    <div class="mb-4">
                        <img src="{{ $auth_banner }}" alt="Banner de autenticação" class="w-full max-h-[260px] object-cover border border-gray-800 rounded-lg" onerror="this.style.display='none'">
                        <div class="flex items-center justify-between text-xs text-[#6B7280] mt-2">
                            <p>Banner atual ({{ $auth_banner_type === 'url' ? 'URL' : 'Arquivo' }})</p>
                            <p>Posição atual: <span class="font-semibold text-white uppercase">{{ $auth_banner_side === 'right' ? 'Direita' : 'Esquerda' }}</span></p>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-[#6B7280] mb-1">Enviar arquivo:</label>
                            <input 
                                type="file" 
                                id="auth_banner_file" 
                                name="auth_banner_file" 
                                accept="image/jpeg,image/jpg,image/png,image/webp"
                                class="block w-full text-sm text-[#6B7280] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#D4AF37]/20 file:text-[#D4AF37] hover:file:bg-[#D4AF37]/30"
                            >
                        </div>
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-700"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-2 bg-[#111111] text-[#6B7280]">OU</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-[#6B7280] mb-1">Usar URL:</label>
                            <input 
                                type="url" 
                                id="auth_banner_url" 
                                name="auth_banner_url" 
                                value="{{ $auth_banner && $auth_banner_type === 'url' ? $auth_banner : '' }}"
                                placeholder="https://exemplo.com/seu-banner.jpg"
                                class="block w-full px-3 py-2 border border-gray-700 bg-[#1a1a1a] text-white rounded-lg focus:ring-2 focus:ring-[#D4AF37] focus:border-[#D4AF37] text-sm"
                            >
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <span class="block text-xs font-medium text-[#6B7280] mb-2">Posição do banner:</span>
                            <div class="flex items-center gap-4">
                                <label class="inline-flex items-center gap-2 text-sm text-[#e5e7eb]">
                                    <input 
                                        type="radio" 
                                        name="auth_banner_side" 
                                        value="left" 
                                        class="form-radio text-[#D4AF37] border-gray-600 bg-transparent focus:ring-[#D4AF37]"
                                        {{ $auth_banner_side !== 'right' ? 'checked' : '' }}
                                    >
                                    <span>Esquerda</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-[#e5e7eb]">
                                    <input 
                                        type="radio" 
                                        name="auth_banner_side" 
                                        value="right" 
                                        class="form-radio text-[#D4AF37] border-gray-600 bg-transparent focus:ring-[#D4AF37]"
                                        {{ $auth_banner_side === 'right' ? 'checked' : '' }}
                                    >
                                    <span>Direita</span>
                                </label>
                            </div>
                        </div>
                        <p class="text-xs text-[#6B7280]">
                            Quando ativado, o banner ocupa metade da tela de login/cadastro e remove os cantos arredondados do formulário para um layout de tela cheia.
                        </p>
                        <p class="text-xs text-[#6B7280]">Formato: JPEG, JPG, PNG ou WebP. Até 5MB. <strong class="text-white">Medida recomendada: 1080x1080px (1:1 - Quadrado)</strong> - O banner ocupará exatamente 50% da largura da tela e 100% da altura, cobrindo toda a área sem espaços pretos.</p>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-800">
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-sm font-medium text-white bg-[#1a1a1a] border border-gray-700 rounded-lg hover:bg-[#1E1E1E] transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-[#D4AF37] rounded-lg hover:bg-[#7A0000] transition-colors">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.getElementById('primary_color');
    const colorText = document.getElementById('primary_color_text');
    
    colorInput.addEventListener('input', function() {
        colorText.value = this.value;
    });
    
    colorText.addEventListener('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            colorInput.value = this.value;
        }
    });
});
</script>
@endsection

