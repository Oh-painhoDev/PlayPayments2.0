@extends('layouts.dashboard')

@section('title', 'Configurações - Documentos')
@section('page-title', 'Verificação de Documentos')
@section('page-description', 'Envie seus documentos para verificação da conta')

@section('content')
<section class="bg-view" style="background-color: #000000 !important; min-height: 100vh; padding: 0; width: 100%;">
<div class="dashboard-container space-y-6" style="background-color: #000000 !important; padding: 1.5rem; width: 100%;">
    <!-- Header Section -->
    <div class="rounded-2xl" style="background-color: rgb(22, 22, 22); padding: 2rem;">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2 text-white">Verificação de Documentos</h1>
                <p class="text-[#707070]">Envie seus documentos para verificação e validação da conta</p>
            </div>
            <div class="w-20 h-20 bg-[#1F1F1F] rounded-2xl flex items-center justify-center">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Status Alert -->
    @if($verification && $verification->isApproved())
        <div class="rounded-2xl" style="background-color: rgb(22, 22, 22); border-left: 4px solid #22C672; padding: 1.5rem;">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 flex-shrink-0 text-[#22C672]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="font-bold text-lg text-white">Documentos Aprovados!</h3>
                    <p class="text-[#707070]">Seus documentos foram verificados e aprovados{{ $verification->reviewed_at ? ' em ' . $verification->formatted_reviewed_at : '' }}.</p>
                </div>
            </div>
        </div>
    @elseif($verification && $verification->isRejected())
        <div class="rounded-2xl" style="background-color: rgb(22, 22, 22); border-left: 4px solid #ff6b6b; padding: 1.5rem;">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 flex-shrink-0 text-[#ff6b6b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="font-bold text-lg text-white">Documentos Rejeitados</h3>
                    <p class="text-[#707070]">{{ $verification->rejection_reason ?? 'Documentos rejeitados. Envie novamente.' }}</p>
                    @if($verification->reviewed_at)
                        <p class="text-sm mt-1 text-[#707070]">Rejeitado em {{ $verification->formatted_reviewed_at }}</p>
                    @endif
                </div>
            </div>
        </div>
    @elseif($verification && $verification->submitted_at && $verification->hasAllDocuments())
        <div class="rounded-2xl" style="background-color: rgb(22, 22, 22); border-left: 4px solid #ffa782; padding: 1.5rem;">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 flex-shrink-0 text-[#ffa782]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="font-bold text-lg text-white">Documentos em Análise</h3>
                    <p class="text-[#707070]">Seus documentos foram enviados e estão sendo analisados. Aguarde a conclusão da verificação.</p>
                    @if($verification->submitted_at)
                        <p class="text-sm mt-1 text-[#707070]">Enviado em {{ $verification->formatted_submitted_at }}</p>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="rounded-2xl" style="background-color: rgb(22, 22, 22); border-left: 4px solid #ffa782; padding: 1.5rem;">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 flex-shrink-0 text-[#ffa782]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <div>
                    <h3 class="font-bold text-lg text-white">Documentos Pendentes</h3>
                    <p class="text-[#707070]">Você precisa enviar os seguintes documentos:</p>
                    @if($verification)
                        <ul class="list-disc list-inside mt-2 text-sm text-[#707070]">
                            @foreach($verification->getMissingDocuments() as $doc)
                                <li>{{ $doc }}</li>
                            @endforeach
                        </ul>
                    @else
                        <ul class="list-disc list-inside mt-2 text-sm text-[#707070]">
                            <li>Frente do documento</li>
                            <li>Verso do documento</li>
                            <li>Selfie com documento</li>
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="rounded-2xl" style="background-color: rgb(22, 22, 22); border-left: 4px solid #22C672; padding: 1.5rem;">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-[#22C672]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-white">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-2xl" style="background-color: rgb(22, 22, 22); border-left: 4px solid #ff6b6b; padding: 1.5rem;">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-[#ff6b6b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-white">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl" style="background-color: rgb(22, 22, 22); border-left: 4px solid #ff6b6b; padding: 1.5rem;">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5 text-[#ff6b6b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h4 class="font-bold mb-1 text-white">Erros encontrados:</h4>
                    <ul class="list-disc list-inside text-sm text-[#707070]">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Document Upload Form -->
            @if($user->canModifyDocuments())
                <form action="{{ route('settings.documents.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Verification Info -->
                    <div class="rounded-2xl" style="background-color: rgb(22, 22, 22); overflow: hidden;">
                        <div style="background-color: rgb(31, 31, 31); padding: 1.5rem; border-bottom: 1px solid rgb(31, 31, 31);">
                            <h3 class="text-xl font-bold text-white flex items-center">
                                <div class="w-10 h-10 bg-[#1F1F1F] rounded-xl flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                Informações da Verificação
                            </h3>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="rounded-xl" style="background-color: rgb(31, 31, 31); padding: 1rem; border: 1px solid rgb(31, 31, 31);">
                                    <span class="text-xs font-bold text-[#707070] uppercase tracking-wide block mb-2">Tipo de Conta</span>
                                    <p class="text-white font-bold">{{ $user->isPessoaFisica() ? 'Pessoa Física' : 'Pessoa Jurídica' }}</p>
                                </div>
                                
                                <div class="rounded-xl" style="background-color: rgb(31, 31, 31); padding: 1rem; border: 1px solid rgb(31, 31, 31);">
                                    <span class="text-xs font-bold text-[#707070] uppercase tracking-wide block mb-2">Status</span>
                                    <span class="inline-flex px-3 py-1 rounded-lg text-xs font-bold
                                        @if($verification && $verification->isApproved()) text-[#22C672]
                                        @elseif($verification && $verification->isRejected()) text-[#ff6b6b]
                                        @else text-[#ffa782] @endif">
                                        {{ $verification ? ucfirst($verification->status) : 'Pendente' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Required Documents -->
                    <div class="rounded-2xl" style="background-color: rgb(22, 22, 22); overflow: hidden;">
                        <div style="background-color: rgb(31, 31, 31); padding: 1.5rem; border-bottom: 1px solid rgb(31, 31, 31);">
                            <h3 class="text-xl font-bold text-white flex items-center">
                                <div class="w-10 h-10 bg-[#1F1F1F] rounded-xl flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                Documentos Obrigatórios
                            </h3>
                        </div>
                        
                        <div class="p-6 space-y-5">
                            <!-- Front Document -->
                            <div class="rounded-xl" style="background-color: rgb(31, 31, 31); padding: 1rem; border: 1px solid rgb(31, 31, 31);">
                                <label class="block text-sm font-bold text-white mb-3">
                                    {{ $user->isPessoaFisica() ? 'Frente do Documento (CPF/RG)' : 'Frente do RG/CNH' }} *
                                </label>
                                <div class="flex items-center space-x-4">
                                    @if($verification && $verification->front_document)
                                        <div class="flex items-center rounded-xl px-4 py-2 font-bold" style="background-color: rgb(22, 22, 22); border: 1px solid #22C672; color: #22C672;">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Enviado
                                        </div>
                                    @endif
                                    <input 
                                        type="file" 
                                        name="front_document" 
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        class="block w-full text-sm text-white file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-[#D4AF37] file:text-white hover:file:bg-[#7a0000] file:cursor-pointer file:transition-all"
                                        style="background-color: rgb(31, 31, 31);"
                                    >
                                </div>
                                <p class="text-xs text-[#707070] mt-2 font-medium">JPG, PNG ou PDF - Máximo 10MB</p>
                            </div>

                            <!-- Back Document -->
                            <div class="rounded-xl" style="background-color: rgb(31, 31, 31); padding: 1rem; border: 1px solid rgb(31, 31, 31);">
                                <label class="block text-sm font-bold text-white mb-3">
                                    {{ $user->isPessoaFisica() ? 'Verso do Documento' : 'Verso do RG/CNH' }} *
                                </label>
                                <div class="flex items-center space-x-4">
                                    @if($verification && $verification->back_document)
                                        <div class="flex items-center rounded-xl px-4 py-2 font-bold" style="background-color: rgb(22, 22, 22); border: 1px solid #22C672; color: #22C672;">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Enviado
                                        </div>
                                    @endif
                                    <input 
                                        type="file" 
                                        name="back_document" 
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        class="block w-full text-sm text-white file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-[#D4AF37] file:text-white hover:file:bg-[#7a0000] file:cursor-pointer file:transition-all"
                                        style="background-color: rgb(31, 31, 31);"
                                    >
                                </div>
                                <p class="text-xs text-[#707070] mt-2 font-medium">JPG, PNG ou PDF - Máximo 10MB</p>
                            </div>

                            <!-- Selfie with Document -->
                            <div class="rounded-xl" style="background-color: rgb(31, 31, 31); padding: 1rem; border: 1px solid rgb(31, 31, 31);">
                                <label class="block text-sm font-bold text-white mb-3">
                                    Selfie com o Documento *
                                </label>
                                <div class="flex items-center space-x-4">
                                    @if($verification && $verification->selfie_document)
                                        <div class="flex items-center rounded-xl px-4 py-2 font-bold" style="background-color: rgb(22, 22, 22); border: 1px solid #22C672; color: #22C672;">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Enviado
                                        </div>
                                    @endif
                                    <input 
                                        type="file" 
                                        name="selfie_document" 
                                        accept=".jpg,.jpeg,.png"
                                        class="block w-full text-sm text-white file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-[#D4AF37] file:text-white hover:file:bg-[#7a0000] file:cursor-pointer file:transition-all"
                                        style="background-color: rgb(31, 31, 31);"
                                    >
                                </div>
                                <p class="text-xs text-[#707070] mt-2 font-medium">📎 Apenas JPG ou PNG - Máximo 10MB</p>
                            </div>

                            @if($user->isPessoaJuridica())
                            <!-- Contrato Social (only for Pessoa Jurídica) -->
                            <div class="rounded-xl" style="background-color: rgb(31, 31, 31); padding: 1rem; border: 1px solid rgb(31, 31, 31);">
                                <label class="block text-sm font-bold text-white mb-3">
                                    Contrato Social *
                                </label>
                                <div class="flex items-center space-x-4">
                                    @if($verification && $verification->proof_address)
                                        <div class="flex items-center rounded-xl px-4 py-2 font-bold" style="background-color: rgb(22, 22, 22); border: 1px solid #22C672; color: #22C672;">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Enviado
                                        </div>
                                    @endif
                                    <input 
                                        type="file" 
                                        name="contrato_social" 
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        class="block w-full text-sm text-white file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-[#D4AF37] file:text-white hover:file:bg-[#7a0000] file:cursor-pointer file:transition-all"
                                        style="background-color: rgb(31, 31, 31);"
                                    >
                                </div>
                                <p class="text-xs text-[#707070] mt-2 font-medium">* arquivos aceitos: .pdf, .jpg, .png</p>
                            </div>
                            @endif

                            <!-- Submit Button -->
                            <div class="mt-6 text-center">
                                <button 
                                    type="submit" 
                                    class="bg-[#D4AF37] hover:bg-[#7a0000] text-white px-10 py-3.5 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl inline-flex items-center"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    Enviar Documentos
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            @elseif($verification && $verification->isApproved())
                <div class="rounded-2xl text-center" style="background-color: rgb(22, 22, 22); padding: 3rem;">
                    <div class="w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6" style="background-color: rgb(31, 31, 31);">
                        <svg class="w-12 h-12 text-[#22C672]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Documentos Bloqueados</h3>
                    <p class="text-[#707070]">
                        Seus documentos foram aprovados e não podem mais ser alterados.
                    </p>
                </div>
            @else
                <div class="rounded-2xl text-center" style="background-color: rgb(22, 22, 22); padding: 3rem;">
                    <div class="w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6" style="background-color: rgb(31, 31, 31);">
                        <svg class="w-12 h-12 text-[#ffa782]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Documentos em Análise</h3>
                    <p class="text-[#707070]">
                        Seus documentos foram enviados e estão sendo analisados. Aguarde a conclusão da verificação.
                    </p>
                </div>
            @endif
        </div>

        <!-- Sidebar: Instructions -->
        <div class="space-y-6">
            <div class="rounded-2xl" style="background-color: rgb(22, 22, 22); padding: 1.5rem;">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center mr-3" style="background-color: rgb(31, 31, 31);">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    Instruções Importantes
                </h3>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-start rounded-xl p-3" style="background-color: rgb(31, 31, 31);">
                        <svg class="w-5 h-5 mr-3 mt-0.5 text-[#22C672] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-white font-medium">Documentos legíveis e válidos</span>
                    </li>
                    <li class="flex items-start rounded-xl p-3" style="background-color: rgb(31, 31, 31);">
                        <svg class="w-5 h-5 mr-3 mt-0.5 text-[#22C672] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-white font-medium">JPG, PNG, PDF (máx 10MB)</span>
                    </li>
                    <li class="flex items-start rounded-xl p-3" style="background-color: rgb(31, 31, 31);">
                        <svg class="w-5 h-5 mr-3 mt-0.5 text-[#22C672] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-white font-medium">Análise em até 2 dias úteis</span>
                    </li>
                    <li class="flex items-start rounded-xl p-3" style="background-color: rgb(31, 31, 31);">
                        <svg class="w-5 h-5 mr-3 mt-0.5 text-[#22C672] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-white font-medium">Notificação por email</span>
                    </li>
                    <li class="flex items-start rounded-xl p-3" style="background-color: rgb(31, 31, 31);">
                        <svg class="w-5 h-5 mr-3 mt-0.5 text-[#22C672] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-white font-medium">Bloqueado após aprovação</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
</section>

@push('styles')
<style>
    .bg-view {
        background-color: #000000 !important;
    }
    .dashboard-container {
        background-color: #000000 !important;
    }
    body {
        background-color: #000000 !important;
    }
    .main-content {
        background-color: #000000 !important;
    }
    .scrollable-content {
        background-color: #000000 !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
$(document).ready(function() {
    $('input[type="file"]').change(function() {
        const file = this.files[0];
        if (file) {
            const maxSize = 10 * 1024 * 1024; // 10MB
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            
            if (file.size > maxSize) {
                alert(`ERRO: Arquivo muito grande (${fileSizeMB}MB)!\n\n` +
                      `Máximo permitido: 10MB\n\n` +
                      `DICA: Comprima seu arquivo antes de enviar:\n` +
                      `• Use TinyPNG.com ou Compressor.io para imagens\n` +
                      `• Reduza a qualidade/resolução da foto\n` +
                      `• Para PDFs, use PDF Compressor online`);
                this.value = '';
                return;
            }

            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            if (!allowedTypes.includes(file.type)) {
                alert('ERRO: Tipo de arquivo não permitido!\n\n' +
                      'Use apenas JPG, PNG ou PDF.');
                this.value = '';
                return;
            }
            
            // Mostrar tamanho do arquivo se tudo estiver ok
            console.log(`Arquivo válido: ${file.name} (${fileSizeMB}MB)`);
        }
    });
});
</script>
@endpush
@endsection
