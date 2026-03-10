/**
 * Alerts JavaScript
 * Funcionalidades para gerenciar alertas e notificações
 */

(function() {
    'use strict';

    /**
     * Auto-hide success alerts after delay
     * @param {number} delay - Delay in milliseconds (default: 5000)
     */
    function autoHideAlerts(delay) {
        delay = delay || 5000;
        
        const successAlerts = document.querySelectorAll('div[style*="background: rgba(0, 255, 0"], div[style*="border: 1px solid #00ff00"]');
        
        successAlerts.forEach(function(alert) {
            setTimeout(function() {
                if (typeof jQuery !== 'undefined' && jQuery.fn.fadeOut) {
                    jQuery(alert).fadeOut();
                } else {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500);
                }
            }, delay);
        });
    }

    /**
     * Initialize alerts functionality
     */
    function initAlerts() {
        // Auto-hide success alerts
        autoHideAlerts(5000);

        // Close button functionality
        const closeButtons = document.querySelectorAll('.alert-close, [data-dismiss="alert"]');
        closeButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const alert = this.closest('.alert, div[style*="background:"]');
                if (alert) {
                    if (typeof jQuery !== 'undefined' && jQuery.fn.fadeOut) {
                        jQuery(alert).fadeOut();
                    } else {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(function() {
                            alert.style.display = 'none';
                        }, 500);
                    }
                }
            });
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAlerts);
    } else {
        initAlerts();
    }
})();





