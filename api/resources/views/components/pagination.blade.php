@php
    // Obter elementos de paginação do paginator se não estiverem disponíveis
    if (!isset($elements)) {
        $elements = [];
        
        // Verificar se é realmente um paginator (não é uma Collection)
        $isCollection = $paginator instanceof \Illuminate\Support\Collection || 
                        $paginator instanceof \Illuminate\Database\Eloquent\Collection;
        
        // Se for uma Collection, não é um paginator válido
        if ($isCollection) {
            $elements = [];
        } elseif (method_exists($paginator, 'hasPages') && 
                  method_exists($paginator, 'currentPage') && 
                  method_exists($paginator, 'lastPage') &&
                  method_exists($paginator, 'url')) {
            // Gerar elementos manualmente usando métodos do paginator
            try {
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                
                if ($lastPage <= 7) {
                    // Mostrar todas as páginas
                    for ($i = 1; $i <= $lastPage; $i++) {
                        $elements[] = [$i => $paginator->url($i)];
                    }
                } else {
                    // Primeira página
                    $elements[] = [1 => $paginator->url(1)];
                    
                    if ($currentPage > 3) {
                        $elements[] = '...';
                    }
                    
                    // Páginas ao redor da atual
                    $start = max(2, $currentPage - 1);
                    $end = min($lastPage - 1, $currentPage + 1);
                    
                    for ($i = $start; $i <= $end; $i++) {
                        $elements[] = [$i => $paginator->url($i)];
                    }
                    
                    if ($currentPage < $lastPage - 2) {
                        $elements[] = '...';
                    }
                    
                    // Última página
                    $elements[] = [$lastPage => $paginator->url($lastPage)];
                }
            } catch (\Throwable $e) {
                // Se falhar, elementos vazios
                $elements = [];
            }
        }
    }
@endphp

@if (method_exists($paginator, 'hasPages') && $paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                    Anterior
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-green-300 focus:border-green-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                    Anterior
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-green-300 focus:border-green-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                    Próxima
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                    Próxima
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700 leading-5">
                    Mostrando
                    <span class="font-medium">{{ $paginator->firstItem() ?? 0 }}</span>
                    até
                    <span class="font-medium">{{ $paginator->lastItem() ?? 0 }}</span>
                    de
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    resultados
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rounded-lg shadow-sm space-x-1">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="Anterior">
                            <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 cursor-not-allowed rounded-lg" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-500 transition-all duration-150" aria-label="Anterior">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default rounded-lg">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-bold text-white bg-gradient-to-r from-green-600 to-emerald-600 border border-green-600 cursor-default rounded-lg shadow-md">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-500 transition-all duration-150" aria-label="Ir para página {{ $page }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-500 transition-all duration-150" aria-label="Próxima">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="Próxima">
                            <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 cursor-not-allowed rounded-lg" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
