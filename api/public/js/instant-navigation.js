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
        
        // Criar link de prefetch
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        link.as = 'document';
        
        // Adicionar ao head
        document.head.appendChild(link);
        
        // Marcar como prefetched
        prefetchedPages.add(url);
        
        // Opcional: fazer fetch manual para garantir cache
        if ('fetch' in window) {
            fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                priority: 'low'
            }).then(response => response.text())
              .then(html => {
                  prefetchCache.set(url, html);
              })
              .catch(() => {
                  // Ignorar erros silenciosamente
              });
        }
        
        console.log('⚡ Prefetched:', url);
    }
    
    // Prefetch automático de páginas comuns do dashboard
    window.addEventListener('load', function() {
        // Delay para não bloquear o carregamento inicial
        setTimeout(() => {
            const commonPages = [
                '/dashboard',
                '/sales',
                '/withdrawals',
                '/settings/profile',
                '/settings/webhooks',
                '/settings/api'
            ];
            
            commonPages.forEach(page => {
                prefetchPage(page);
            });
        }, 1000);
    });
    
    // Adicionar classe de transição suave
    document.addEventListener('DOMContentLoaded', function() {
        document.body.style.transition = 'opacity 0.15s ease-in-out';
    });
    
})();
