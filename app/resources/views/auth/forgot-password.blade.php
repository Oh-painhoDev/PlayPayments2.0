@extends('layouts.app')

@section('title', 'Recuperar Senha')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}" type="text/css" media="all">
@endpush

@section('content')

<section class="bg-auth">
    <figure>
        <img src="{{ $whiteLabelLogo ?? '/images/playpayments-logo-top.webp' }}" alt="$playpayments - Logo">
    </figure>
    
    <section class="bg-modal">
        <h2>Recuperar Senha</h2>
        <p>Informe seu e-mail para receber o link de recuperação</p>
        
        @if (session('status'))
            <div style="background: rgba(0, 255, 0, 0.1); border: 1px solid #00ff00; border-radius: 8px; padding: 12px; margin-bottom: 20px; color: #00ff00;">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="background: rgba(106, 0, 0, 0.1); border: 1px solid #D4AF37; border-radius: 8px; padding: 12px; margin-bottom: 20px; color: #D4AF37;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <section class="form">
                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label" for="email">E-mail</label>
                    </div>
                    <div class="input-group">
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="default-input input" 
                            placeholder="Ex: seucontato@suaempresa.com.br" 
                            maxlength="256" 
                            autocomplete="email" 
                            value="{{ old('email') }}"
                            required
                        >
                    </div>
                    @error('email')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </section>

                <section class="actions">
                    <section class="primary bg-large-button">
                        <button type="submit">Enviar Link de Recuperação</button>
                    </section>
                </section>

                <div style="text-align: center; margin-top: 20px;">
                    <a href="{{ route('login') }}" style="color: #86898B; text-decoration: none; font-size: 14px;">Voltar para o login</a>
                </div>
            </section>
        </form>
    </section>
</section>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="{{ asset('js/alerts.js') }}"></script>
@endpush
@endsection
