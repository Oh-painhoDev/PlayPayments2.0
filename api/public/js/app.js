// App JavaScript - PHP Puro
// Substitui recursos que dependiam de Axios e módulos ES6

// Helper function para fazer requisições fetch com CSRF token
window.makeRequest = function(url, options = {}) {
    const headers = options.headers || {};
    headers['X-Requested-With'] = 'XMLHttpRequest';
    headers['Content-Type'] = headers['Content-Type'] || 'application/json';
    headers['Accept'] = headers['Accept'] || 'application/json';
    
    // Adicionar CSRF token se disponível
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    }
    
    // Adicionar auth token se disponível
    const authToken = localStorage.getItem('auth_token');
    if (authToken) {
        headers['X-Auth-Token'] = authToken;
    }
    
    return fetch(url, {
        ...options,
        headers: headers
    });
};

// Compatibilidade com código que pode usar axios
window.axios = {
    get: function(url, config) {
        return makeRequest(url, {
            method: 'GET',
            headers: config?.headers || {}
        }).then(response => response.json());
    },
    post: function(url, data, config) {
        return makeRequest(url, {
            method: 'POST',
            body: JSON.stringify(data),
            headers: config?.headers || {}
        }).then(response => response.json());
    },
    put: function(url, data, config) {
        return makeRequest(url, {
            method: 'PUT',
            body: JSON.stringify(data),
            headers: config?.headers || {}
        }).then(response => response.json());
    },
    delete: function(url, config) {
        return makeRequest(url, {
            method: 'DELETE',
            headers: config?.headers || {}
        }).then(response => response.json());
    },
    defaults: {
        headers: {
            common: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }
    },
    interceptors: {
        request: {
            use: function(callback) {
                // Interceptor simples para compatibilidade
                window.requestInterceptor = callback;
            }
        }
    }
};

