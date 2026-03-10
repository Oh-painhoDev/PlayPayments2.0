/**
 * Instant Navigation System
 * Prefetch pages on hover for instantaneous navigation
 */

(function() {
    'use strict';
    
    const prefetchedPages = new Set();
    const prefetchCache = new Map();
    let prefetchTimeout;
    
    // Detectar hover nos links
    document.addEventListener('mouseover', function(e) {
        const link = e.target.closest('a[href]');
        
        if (!link) return;
        
        const href = link.getAttribute('href');
        
        // Ignorar links externos, âncoras, javascript
        if (!href || 
            href.startsWith('#') || 
            href.startsWith('javascript:') ||
            href.startsWith('mailto:') ||
            href.startsWith('tel:') ||
            link.target === '_blank' ||
            href.includes('://') && !href.includes(window.location.hostname)) {
            return;
        }
        
        // Prefetch com delay de 65ms (tempo médio antes do clique)
        clearTimeout(prefetchTimeout);
        prefetchTimeout = setTimeout(() => {
            prefetchPage(href);
        }, 65);
    });
    
    // Limpar timeout se sair do link
    document.addEventListener('mouseout', function(e) {
        const link = e.target.closest('a[href]');
        if (link) {
            clearTimeout(prefetchTimeout);
        }
    });
    
    function prefetchPage(url) {
        // Já foi prefetched
        if (prefetchedPages.has(url)) return;
        
        // Marcar como prefetched imediatamente para evitar duplicatas
        prefetchedPages.add(url);
        
        // Usar fetch com baixa prioridade em vez de link prefetch
        // Isso evita warnings de preload não utilizado
        if ('fetch' in window) {
            fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                priority: 'low',
                cache: 'force-cache'
            }).then(response => {
                if (response.ok) {
                    return response.text();
                }
                throw new Error('Response not ok');
            })
            .then(html => {
                prefetchCache.set(url, html);
                console.log('⚡ Prefetched:', url);
            })
            .catch(() => {
                // Ignorar erros silenciosamente e remover do set se falhar
                prefetchedPages.delete(url);
            });
        }
    }
    
    // Prefetch automático de páginas comuns do dashboard
    // Removido para evitar warnings de preload não utilizado
    // O prefetch será feito apenas no hover dos links
    
    // Adicionar classe de transição suave
    document.addEventListener('DOMContentLoaded', function() {
        document.body.style.transition = 'opacity 0.15s ease-in-out';
    });
    
})();
