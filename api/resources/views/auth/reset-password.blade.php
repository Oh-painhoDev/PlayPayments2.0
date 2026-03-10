@extends('layouts.app')

@section('title', 'Redefinir Senha')

@push('styles')
<link rel="stylesheet" href="/css/login.css" type="text/css" media="all">
@endpush

@section('content')

<section class="bg-auth">
    <figure>
        <img src="/images/playpayments-logo-top.webp" alt="playpayments Payments - Logo">
    </figure>
    
    <section class="bg-modal">
        <h2>Redefinir Senha</h2>
        <p>Crie uma nova senha para sua conta</p>
        
        @if (session('status'))
            <div style="background: rgba(0, 255, 0, 0.1); border: 1px solid #00ff00; border-radius: 8px; padding: 12px; margin-bottom: 20px; color: #00ff00;">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="background: rgba(106, 0, 0, 0.1); border: 1px solid #21b3dd; border-radius: 8px; padding: 12px; margin-bottom: 20px; color: #21b3dd;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            
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
                            value="{{ $email ?? old('email') }}"
                            required
                        >
                    </div>
                    @error('email')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </section>

                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label" for="password">Nova Senha</label>
                    </div>
                    <div class="input-group">
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            class="default-input input" 
                            placeholder="Digite sua nova senha" 
                            maxlength="256" 
                            autocomplete="new-password" 
                            required
                        >
                        <div class="show-text" onclick="togglePassword('password')">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path id="eye-path-password" opacity="0.5" d="M2 12C2 13.64 2.425 14.191 3.275 15.296C4.972 17.5 7.818 20 12 20C16.182 20 19.028 17.5 20.725 15.296C21.575 14.192 22 13.639 22 12C22 10.36 21.575 9.809 20.725 8.704C19.028 6.5 16.182 4 12 4C7.818 4 4.972 6.5 3.275 8.704C2.425 9.81 2 10.361 2 12Z" fill="#86898B"></path>
                                <path id="eye-inner-password" fill-rule="evenodd" clip-rule="evenodd" d="M8.25 12C8.25 11.0054 8.64509 10.0516 9.34835 9.34835C10.0516 8.64509 11.0054 8.25 12 8.25C12.9946 8.25 13.9484 8.64509 14.6517 9.34835C15.3549 10.0516 15.75 11.0054 15.75 12C15.75 12.9946 15.3549 13.9484 14.6517 14.6517C13.9484 15.3549 12.9946 15.75 12 15.75C11.0054 15.75 10.0516 15.3549 9.34835 14.6517C8.64509 13.9484 8.25 12.9946 8.25 12ZM9.75 12C9.75 11.4033 9.98705 10.831 10.409 10.409C10.831 9.98705 11.4033 9.75 12 9.75C12.5967 9.75 13.169 9.98705 13.591 10.409C14.0129 10.831 14.25 11.4033 14.25 12C14.25 12.5967 14.0129 13.169 13.591 13.591C13.169 14.0129 12.5967 14.25 12 14.25C11.4033 14.25 10.831 14.0129 10.409 13.591C9.98705 13.169 9.75 12.5967 9.75 12Z" fill="#86898B"></path>
                            </svg>
                        </div>
                    </div>
                    @error('password')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </section>

                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label" for="password_confirmation">Confirmar Nova Senha</label>
                    </div>
                    <div class="input-group">
                        <input 
                            type="password" 
                            name="password_confirmation" 
                            id="password_confirmation"
                            class="default-input input" 
                            placeholder="Confirme sua nova senha" 
                            maxlength="256" 
                            autocomplete="new-password" 
                            required
                        >
                        <div class="show-text" onclick="togglePassword('password_confirmation')">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path id="eye-path-password_confirmation" opacity="0.5" d="M2 12C2 13.64 2.425 14.191 3.275 15.296C4.972 17.5 7.818 20 12 20C16.182 20 19.028 17.5 20.725 15.296C21.575 14.192 22 13.639 22 12C22 10.36 21.575 9.809 20.725 8.704C19.028 6.5 16.182 4 12 4C7.818 4 4.972 6.5 3.275 8.704C2.425 9.81 2 10.361 2 12Z" fill="#86898B"></path>
                                <path id="eye-inner-password_confirmation" fill-rule="evenodd" clip-rule="evenodd" d="M8.25 12C8.25 11.0054 8.64509 10.0516 9.34835 9.34835C10.0516 8.64509 11.0054 8.25 12 8.25C12.9946 8.25 13.9484 8.64509 14.6517 9.34835C15.3549 10.0516 15.75 11.0054 15.75 12C15.75 12.9946 15.3549 13.9484 14.6517 14.6517C13.9484 15.3549 12.9946 15.75 12 15.75C11.0054 15.75 10.0516 15.3549 9.34835 14.6517C8.64509 13.9484 8.25 12.9946 8.25 12ZM9.75 12C9.75 11.4033 9.98705 10.831 10.409 10.409C10.831 9.98705 11.4033 9.75 12 9.75C12.5967 9.75 13.169 9.98705 13.591 10.409C14.0129 10.831 14.25 11.4033 14.25 12C14.25 12.5967 14.0129 13.169 13.591 13.591C13.169 14.0129 12.5967 14.25 12 14.25C11.4033 14.25 10.831 14.0129 10.409 13.591C9.98705 13.169 9.75 12.5967 9.75 12Z" fill="#86898B"></path>
                            </svg>
                        </div>
                    </div>
                    @error('password_confirmation')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </section>

                <section class="actions">
                    <section class="primary bg-large-button">
                        <button type="submit">Redefinir Senha</button>
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
<script src="/js/auth.js"></script>
<script src="/js/alerts.js"></script>
@endpush
@endsection
