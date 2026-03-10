# 🍎 macOS Dock Navigation - PlayPayments 2.0

## 📋 Overview

O macOS Dock é uma barra de navegação flutuante e elegante inspirada no design do macOS, implementada em todo o sistema PlayPayments 2.0. Oferece uma experiência imersiva com efeito de zoom magnético (magnifying glass effect) ao passar o mouse sobre os ícones.

## ✨ Features

### 1. **Magnifying Glass Effect**
- Ícones escalam até 2.7x quando em hover
- Vizinhos escalam proporcionalmente (efeito de onda)
- Animação suave com transição de 0.4s

### 2. **Glassmorphism Design**
- Fundo com blur efeito (8px backdrop-filter)
- Borda sutil com transparência
- Sombra sofisticada
- Tema dark-first com cores PlayPayments

### 3. **Ícones Dinâmicos**
- Usa Remix Icons (24+ ícones disponíveis)
- Ícones coloridos com diferentes cores por seção
- Indicador de item ativo com dot pulsante

### 4. **Responsividade**
- Desktop: Posição fixa no rodapé com width automático
- Tablet: Tamanho ajustado (--w: 3.2rem)
- Mobile: Versão compacta com font reduzida

## 🎨 Design System

### Cores
```css
Gold/Primary:     #D4AF37    /* Logo, Dashboard, Profile */
Green/Success:    #22C672    /* Transações positivas */
Orange/Warning:   #ffa782    /* Saques */
Blue/Info:        #00d4ff    /* PIX, Payouts */
Gray/Secondary:   #a1a1aa    /* Default icon color */
Background:       rgba(0,0,0,0.3)  /* Dark glassmorphic */
```

### Dimensões
```css
Desktop:  --w: 3.8rem (60-61px)
Tablet:   --w: 3.2rem (51-52px)
Mobile:   --w: 2.8rem (44-45px)
```

## 📍 Componentes

### 1. Área Principal (5 itens)
- **Dashboard** - `ri-dashboard-3-line` - Visão geral
- **Transações** - `ri-shuffle-line` - Todas as transações
- **Clientes** - `ri-user-follow-line` - Lista de clientes/recebedores
- **PIX** - `ri-lightning-charge-line` - Geração de PIX
- **Saques** - `ri-send-plane-line` - Retiradas/Receitas

### 2. Área Pós-Separador (Utilidades)
- **Integrações** - `ri-plug-2-line` - API Keys e webhooks
- **Configurações** - `ri-settings-4-line` - Preferências do sistema
- **Perfil** - `ri-user-circle-line` - Dados do usuário
- **Sair** - `ri-logout-circle-line` - Logout com confirmação

## 🔧 Implementação Técnica

### Arquivos Criados
```
/public/css/macos-dock.css              (450+ linhas CSS)
/resources/views/components/MacosDock.blade.php    (130+ linhas PHP/HTML)
```

### Integração no Layout
```blade
<!-- No head -->
<link rel="stylesheet" href="{{ asset('css/macos-dock.css') }}">

<!-- No body (antes de </body>) -->
@include('components.MacosDock')
```

### Dependências
- **Remix Icons**: CDN (cdn.jsdelivr.net)
- **Blade Components**: Laravel 8+
- **CSS Variables**: :root customizáveis
- **Backdrop Filter**: Navegadores modernos (Safari 9+, Chrome 76+)

## 🎯 Comportamento

### Hover Effects
```javascript
// Ícone principal
Scale: 2.7x
Margin: 1.5rem

// +1 vizinho
Scale: 2.2x
Margin: 0.8rem

// +2 vizinhos
Scale: 1.8x
Margin: 0.5rem

// +3 vizinhos
Scale: 1.3x
Margin: 0.2rem

// +4 vizinhos
Scale: 1.15x
Margin: 0.1rem
```

### Active State
- Indicador visual: ponto pulsante dourado na base
- Cor do ícone muda para #D4AF37
- Brilho sutil (text-shadow)

### Labels
- Aparecem 2.5rem acima do ícone ao hover
- Fundo semitransparente com sombra
- Arrow pointer apontando para ícone
- Fade animation (0.4s ease)

## 📱 Responsividade

### Desktop (1025px+)
```css
Width: calc(9 * 3.8rem) = 342px aprox
Position: fixed bottom 1rem, centered
Gap: 0.5rem
```

### Tablet (768px - 1024px)
```css
Width: Proporcional (3.2rem por item)
Position: Mesma
```

### Mobile (< 480px)
```css
Width: Proporcional (2.8rem por item)
Altura Labels: Reduzida
Font-size: 0.65rem
```

## 🔐 Segurança

### Logout
- Confirmação modal antes de sair
- POST com CSRF token
- Form submit via JavaScript

### Routes
- Todas as rotas com fallback '#'
- Ativos baseados em `request()->routeIs()`
- Proteção por middleware padrão Laravel

## 🎨 Customização

### Adicionar novo item

Edite `resources/views/components/MacosDock.blade.php`:

```blade
<a href="{{ route('seu-route') }}" class="dock-item {{ request()->routeIs('seu-route') ? 'active' : '' }}" title="Seu Item">
    <div class="dock-icon">
        <i class="ri-seu-icon"></i>
    </div>
    <span class="dock-label">Seu Label</span>
</a>
```

### Alterar cores

Edite `public/css/macos-dock.css`:

```css
.dock-item:hover .dock-icon i {
    color: #SUA-COR;
    text-shadow: 0 0 10px rgba(R, G, B, 0.3);
}
```

### Ajustar transições

```css
:root {
  --dock-transition: 0.3s ease; /* Mais rápido */
}
```

## 🚀 Performance

### Otimizações
- CSS direto (sem @import lenta)
- Animações via transform (GPU accelerated)
- Lazy loading de icons
- Z-index gerenciado adequadamente

### Bundle Size
- CSS: ~15KB (comprimido: ~4KB)
- JS: ~3KB (essencial apenas)
- Icons: CDN servido (não incluído no bundle)

## 🐛 Debugging

### Verificar ícones não carregando
```javascript
// Console
ri.init(); // Reinicia Remix Icons
```

### Ajustar position/sizing
Edite a seção "Desktop size adjustment" em `macos-dock.css`

### Testar responsividade
Use DevTools (F12) → Responsive Design Mode

## 📚 Referências

- **Codepen Original**: https://codepen.io/ghaste/pen/ZEMedGV
- **Remix Icons**: https://remixicon.com
- **CSS Backdrop Filter**: https://developer.mozilla.org/en-US/docs/Web/CSS/backdrop-filter
- **Magnifying Glass Effect**: Inspirado no macOS Dock

## ✅ Checklist de Implementação

- ✅ CSS criado e otimizado
- ✅ Componente Blade funcional
- ✅ Integração no layout dashboard
- ✅ Ícones Remix carregando
- ✅ Responsividade testada
- ✅ Active states funcionando
- ✅ Logout com confirmação
- ✅ Performance otimizada
- ✅ Documentação completa

---

**Versão**: 1.0.0  
**Data**: 2026-03-10  
**Status**: ✅ Production Ready
