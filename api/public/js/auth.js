/**
 * Authentication JavaScript
 * Funcionalidades para login, registro, recuperação de senha e onboarding
 */

(function() {
    'use strict';

    /**
     * Toggle password visibility
     * @param {string} fieldId - ID do campo de senha
     */
    window.togglePassword = function(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        const eyePath = document.getElementById('eye-path-' + fieldId);
        const eyeInner = document.getElementById('eye-inner-' + fieldId);
        
        if (field.type === 'password') {
            field.type = 'text';
            // Ícone de olho fechado (mostrar senha)
            if (eyePath) {
                eyePath.setAttribute('d', 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21');
                eyePath.setAttribute('fill', '#86898B');
                eyePath.removeAttribute('opacity');
            }
            if (eyeInner) {
                eyeInner.style.display = 'none';
            }
        } else {
            field.type = 'password';
            // Ícone de olho aberto (ocultar senha)
            if (eyePath) {
                eyePath.setAttribute('d', 'M2 12C2 13.64 2.425 14.191 3.275 15.296C4.972 17.5 7.818 20 12 20C16.182 20 19.028 17.5 20.725 15.296C21.575 14.192 22 13.639 22 12C22 10.36 21.575 9.809 20.725 8.704C19.028 6.5 16.182 4 12 4C7.818 4 4.972 6.5 3.275 8.704C2.425 9.81 2 10.361 2 12Z');
                eyePath.setAttribute('opacity', '0.5');
            }
            if (eyeInner) {
                eyeInner.style.display = 'block';
            }
        }
    };

    /**
     * Switch between login and register tabs
     * @param {string} tab - 'login' or 'register'
     * @param {Event} evt - Optional event object
     */
    window.switchTab = function(tab, evt) {
        const tabs = document.querySelectorAll('.tab');
        const loginContent = document.getElementById('login-content');
        const registerContent = document.getElementById('register-content');
        
        // Update tabs
        tabs.forEach(t => t.classList.remove('active'));
        
        if (evt && evt.target) {
            const clickedTab = evt.target.closest('.tab');
            if (clickedTab) {
                clickedTab.classList.add('active');
            }
        } else {
            // Determine which tab to activate based on tab parameter
            if (tab === 'register' && tabs[0]) {
                tabs[0].classList.add('active');
            } else if (tab === 'login' && tabs[1]) {
                tabs[1].classList.add('active');
            } else if (tabs.length > 0) {
                // Default to first tab if tab parameter doesn't match
                tabs[0].classList.add('active');
            }
        }
        
        // Update content
        if (loginContent && registerContent) {
            loginContent.style.display = tab === 'login' ? 'block' : 'none';
            registerContent.style.display = tab === 'register' ? 'block' : 'none';
        }
    };

    /**
     * Initialize auth page functionality
     */
    function initAuth() {
        // Wait for jQuery if it's being used
        if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(function($) {
                // Máscara WhatsApp se o campo existir
                const whatsappField = $('#reg-whatsapp');
                if (whatsappField.length && $.fn.mask) {
                    whatsappField.mask('(00) 00000-0000');
                }

                // Máscara CEP se o campo existir
                const cepField = $('#onb-cep');
                if (cepField.length && $.fn.mask) {
                    cepField.mask('00000-000');
                }

                // Máscara de documento (CPF/CNPJ) se o campo existir
                const documentField = $('#onb-document');
                if (documentField.length && $.fn.mask) {
                    // A máscara será aplicada dinamicamente pelo updateDocumentField
                }
            });
        }

        // Auto-show register tab if there are registration errors
        const urlParams = new URLSearchParams(window.location.search);
        const showRegister = urlParams.get('register') === 'true' || 
                           document.body.getAttribute('data-show-register') === 'true';
        
        if (showRegister) {
            const tabs = document.querySelectorAll('.tab');
            const loginContent = document.getElementById('login-content');
            const registerContent = document.getElementById('register-content');
            
            if (tabs.length >= 2 && loginContent && registerContent) {
                tabs[0].classList.add('active');
                tabs[1].classList.remove('active');
                loginContent.style.display = 'none';
                registerContent.style.display = 'block';
            }
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAuth);
    } else {
        initAuth();
    }
})();

