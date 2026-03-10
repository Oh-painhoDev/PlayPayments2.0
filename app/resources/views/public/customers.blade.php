@extends('layouts.app')

@section('content')
<div class="container">
    <h1 style="margin-bottom: 30px; color: #333;">👥 Clientes</h1>

    {{-- BUSCA --}}
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <form method="GET" action="{{ route('customers.index') }}" style="display: flex; gap: 10px;">
            <input type="text" 
                   name="search" 
                   placeholder="Buscar por Nome, E-mail, CPF ou Telefone..." 
                   value="{{ request('search') }}"
                   style="flex: 1; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
            <button type="submit" 
                    style="padding: 12px 30px; background: #667eea; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
                🔍 Buscar
            </button>
            @if(request('search'))
                <a href="{{ route('customers.index') }}" 
                   style="padding: 12px 20px; background: #6c757d; color: white; border: none; border-radius: 8px; text-decoration: none; font-weight: bold;">
                    Limpar
                </a>
            @endif
        </form>
    </div>

    {{-- LISTA DE CLIENTES --}}
    <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        @if($clientes && $clientes->count() > 0)
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Nome</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">E-mail</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">CPF</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Telefone</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Transações</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientes as $cliente)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px; color: #333; font-weight: 500;">{{ $cliente->nome }}</td>
                                <td style="padding: 12px; color: #666;">{{ $cliente->email }}</td>
                                <td style="padding: 12px; color: #666;">{{ $cliente->cpf ?? '-' }}</td>
                                <td style="padding: 12px; color: #666;">{{ $cliente->telefone ?? '-' }}</td>
                                <td style="padding: 12px; color: #666;">
                                    <span style="background: #e7f3ff; color: #1976D2; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                                        {{ $cliente->transacoes->count() ?? 0 }}
                                    </span>
                                </td>
                                <td style="padding: 12px;">
                                    <a href="{{ route('customers.show', $cliente->id) }}" 
                                       style="color: #667eea; text-decoration: none; font-weight: bold;">
                                        Ver Detalhes →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- PAGINAÇÃO --}}
            @if(method_exists($clientes, 'links'))
                <div style="margin-top: 20px; display: flex; justify-content: center;">
                    {{ $clientes->links() }}
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 60px 20px;">
                <div style="font-size: 64px; margin-bottom: 20px;">📭</div>
                <h3 style="color: #666; margin: 0 0 10px 0;">Nenhum cliente encontrado</h3>
                <p style="color: #999;">Comece a processar pagamentos para ver seus clientes aqui.</p>
            </div>
        @endif
    </div>
</div>
@endsection

