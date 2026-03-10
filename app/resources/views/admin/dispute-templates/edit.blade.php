@extends('layouts.admin')

@section('title', 'Editar Template de Infração')
@section('page-title', 'Editar Template de Infração')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 via-pink-500 to-red-500 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">✏️ Editar Template</h1>
                <p class="text-purple-100">Atualize as informações do template</p>
            </div>
            <a href="{{ route('admin.setup.dispute-templates.index') }}" class="bg-white hover:bg-gray-100 text-purple-700 px-6 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-4">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-red-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    @foreach($errors->all() as $error)
                        <p class="text-red-800 font-semibold">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.setup.dispute-templates.update', $disputeTemplate) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.dispute-templates.form', ['template' => $disputeTemplate])
        
        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.setup.dispute-templates.index') }}" 
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold transition-all">
                Cancelar
            </a>
            <button type="submit" 
                class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-8 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl">
                Atualizar Template
            </button>
        </div>
    </form>
</div>
@endsection
