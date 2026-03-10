// Theme Toggle - Sistema de alternância entre tema claro e escuro
(function() {
    'use strict';
    
    // Verificar tema salvo no localStorage ou usar tema escuro como padrão
    const savedTheme = localStorage.getItem('theme') || 'dark';
    
    // Aplicar tema ao carregar a página
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        
        // Atualizar ícone do botão
        updateThemeButton(theme);
    }
    
    // Atualizar ícone do botão de tema
    function updateThemeButton(theme) {
        const themeButton = document.getElementById('theme-toggle-btn');
        const themeButtonMobile = document.getElementById('theme-toggle-btn-mobile');
        
        const buttons = [themeButton, themeButtonMobile].filter(Boolean);
        
        buttons.forEach(button => {
            const icon = button.querySelector('svg');
            if (!icon) return;
            
            // Remover todas as classes de ícone
            icon.innerHTML = '';
            
            if (theme === 'light') {
                // Ícone de lua (para alternar para escuro)
                icon.innerHTML = `
                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                `;
            } else {
                // Ícone de sol (para alternar para claro)
                icon.innerHTML = `
                    <circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"></circle>
                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
                `;
            }
        });
    }
    
    // Alternar tema
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        applyTheme(newTheme);
    }
    
    // Inicializar tema ao carregar
    document.addEventListener('DOMContentLoaded', function() {
        applyTheme(savedTheme);
        
        // Adicionar evento aos botões de tema (desktop e mobile)
        const themeButton = document.getElementById('theme-toggle-btn');
        const themeButtonMobile = document.getElementById('theme-toggle-btn-mobile');
        
        if (themeButton) {
            themeButton.addEventListener('click', toggleTheme);
        }
        
        if (themeButtonMobile) {
            themeButtonMobile.addEventListener('click', toggleTheme);
        }
    });
    
    // Exportar função para uso global
    window.toggleTheme = toggleTheme;
    window.applyTheme = applyTheme;
})();

