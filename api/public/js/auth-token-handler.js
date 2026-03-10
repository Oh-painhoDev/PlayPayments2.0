/**
 * Auth Token Handler for Iframe Compatibility
 * Gerencia tokens de autenticação via URL e localStorage
 */

(function() {
    'use strict';

    /**
     * Initialize auth token handler
     */
    function initAuthTokenHandler() {
        // Get auth token from URL
        const urlParams = new URLSearchParams(window.location.search);
        const authToken = urlParams.get('auth_token');
        
        // Store token in localStorage if present
        if (authToken) {
            localStorage.setItem('auth_token', authToken);
            
            // Remove token from URL for cleaner appearance
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
        
        // Get stored token
        const storedToken = localStorage.getItem('auth_token');
        
        // Add token to all internal links
        if (storedToken) {
            document.addEventListener('DOMContentLoaded', function() {
                // Intercept all internal links
                document.addEventListener('click', function(e) {
                    const link = e.target.closest('a');
                    if (link && link.hostname === window.location.hostname) {
                        const href = link.getAttribute('href');
                        if (href && !href.includes('auth_token=') && !href.startsWith('#')) {
                            e.preventDefault();
                            const separator = href.includes('?') ? '&' : '?';
                            window.location.href = href + separator + 'auth_token=' + storedToken;
                        }
                    }
                });
                
                // Intercept all forms
                document.addEventListener('submit', function(e) {
                    const form = e.target;
                    if (form.method.toLowerCase() === 'get') {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'auth_token';
                        input.value = storedToken;
                        form.appendChild(input);
                    }
                });
            });
            
            // Helper function para fazer requisições fetch com token
            window.makeRequest = function(url, options = {}) {
                const headers = options.headers || {};
                headers['X-Auth-Token'] = storedToken;
                headers['X-Requested-With'] = 'XMLHttpRequest';
                headers['Content-Type'] = headers['Content-Type'] || 'application/json';
                headers['Accept'] = headers['Accept'] || 'application/json';
                
                // Adicionar CSRF token se disponível
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
                }
                
                return fetch(url, {
                    ...options,
                    headers: headers
                });
            };
        }
    }

    // Initialize immediately
    initAuthTokenHandler();
})();





