@extends('layouts.app')

@section('content')
<div class="container">
    <h1 style="margin-bottom: 30px; color: #333;">🔌 Integrações</h1>

    <div style="background: #e7f3ff; padding: 20px; border-radius: 12px; margin-bottom: 30px; border-left: 4px solid #2196F3;">
        <p style="margin: 0; color: #1976D2;">
            ℹ️ Use estas chaves para integrar nossa API no seu sistema. Mantenha-as seguras e não compartilhe com terceiros.
        </p>
    </div>

    {{-- TOKENS DA API --}}
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <h2 style="margin-top: 0; color: #333; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 24px;">🔑</span> Chaves de API
        </h2>

        {{-- PUBLIC KEY --}}
        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 10px; color: #666; font-weight: 500; font-size: 14px;">
                Public Key (Chave Pública)
            </label>
            <div style="display: flex; gap: 10px;">
                <input type="text" 
                       id="publicKey" 
                       value="{{ $user->api_public_key ?? 'Não configurada' }}" 
                       readonly 
                       style="flex: 1; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 14px; font-family: monospace; background: #f8f9fa; color: #333;">
                <button onclick="copiarTexto('publicKey')" 
                        style="padding: 12px 20px; background: #667eea; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; white-space: nowrap;">
                    📋 Copiar
                </button>
            </div>
        </div>

        {{-- SECRET KEY --}}
        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 10px; color: #666; font-weight: 500; font-size: 14px;">
                Secret Key (Chave Secreta) 
                <span style="color: #dc3545; font-size: 12px;">⚠️ Mantenha em segredo!</span>
            </label>
            <div style="display: flex; gap: 10px;">
                <input type="password" 
                       id="secretKey" 
                       value="{{ $user->api_secret_key ?? 'Não configurada' }}" 
                       readonly 
                       style="flex: 1; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 14px; font-family: monospace; background: #f8f9fa; color: #333;">
                <button onclick="copiarTexto('secretKey')" 
                        style="padding: 12px 20px; background: #667eea; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; white-space: nowrap;">
                    📋 Copiar
                </button>
                <button onclick="toggleSecretKey()" 
                        style="padding: 12px 20px; background: #6c757d; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; white-space: nowrap;">
                    👁️ Mostrar
                </button>
            </div>
        </div>
    </div>

    {{-- DOCUMENTAÇÃO DA API --}}
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-top: 0; color: #333; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 24px;">📚</span> Como Usar
        </h2>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px;">
            <h3 style="margin-top: 0; color: #333; font-size: 16px;">Autenticação Basic Auth</h3>
            <p style="color: #666; margin: 10px 0;">Use Basic Authentication nas suas requisições:</p>
            <div style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 13px; overflow-x: auto; margin-top: 10px;">
                <div>Username: <span style="color: #a6e22e;">{{ $user->api_public_key ?? 'SUA_PUBLIC_KEY' }}</span></div>
                <div>Password: <span style="color: #a6e22e;">{{ $user->api_secret_key ?? 'SUA_SECRET_KEY' }}</span></div>
            </div>
        </div>

        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px;">
            <h3 style="margin-top: 0; color: #333; font-size: 16px;">Endpoint de Exemplo</h3>
            <div style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 13px; overflow-x: auto; margin-top: 10px;">
                <div style="color: #66d9ef;">POST</div>
                <div style="color: #f92672;">https://api.ganhadinheiro.site/api/v1/payment/pix/create</div>
            </div>
        </div>
    </div>
</div>

<script>
function copiarTexto(inputId) {
    const input = document.getElementById(inputId);
    input.select();
    input.setSelectionRange(0, 99999);
    
    try {
        document.execCommand('copy');
        alert('✅ Copiado com sucesso!');
    } catch (err) {
        navigator.clipboard.writeText(input.value).then(function() {
            alert('✅ Copiado com sucesso!');
        }, function(err) {
            alert('❌ Erro ao copiar. Tente selecionar e copiar manualmente.');
        });
    }
}

function toggleSecretKey() {
    const input = document.getElementById('secretKey');
    const button = event.target;
    
    if (input.type === 'password') {
        input.type = 'text';
        button.textContent = '🙈 Ocultar';
    } else {
        input.type = 'password';
        button.textContent = '👁️ Mostrar';
    }
}
</script>
@endsection

