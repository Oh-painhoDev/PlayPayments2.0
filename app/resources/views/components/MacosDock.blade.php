<!-- macOS Dock Component -->
<div class="macos-dock-container" id="macosDock">
    <!-- Dashboard -->
    <a href="{{ route('dashboard') }}" class="dock-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Dashboard">
        <div class="dock-icon">
            <i class="ri-dashboard-3-line"></i>
        </div>
        <span class="dock-label">Dashboard</span>
    </a>

    <!-- Transactions -->
    <a href="{{ route('transactions.index') ?? '#' }}" class="dock-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}" title="Transactions">
        <div class="dock-icon">
            <i class="ri-shuffle-line"></i>
        </div>
        <span class="dock-label">Transações</span>
    </a>

    <!-- Customers -->
    <a href="{{ route('customers') ?? '#' }}" class="dock-item {{ request()->routeIs('customers') ? 'active' : '' }}" title="Customers">
        <div class="dock-icon">
            <i class="ri-user-follow-line"></i>
        </div>
        <span class="dock-label">Clientes</span>
    </a>

    <!-- PIX -->
    <a href="{{ route('pix.index') ?? '#' }}" class="dock-item {{ request()->routeIs('pix.*') ? 'active' : '' }}" title="PIX">
        <div class="dock-icon">
            <i class="ri-lightning-charge-line"></i>
        </div>
        <span class="dock-label">PIX</span>
    </a>

    <!-- Payouts -->
    <a href="{{ route('revenues') ?? route('wallet.index') ?? '#' }}" class="dock-item {{ request()->routeIs('revenues') ? 'active' : '' }}" title="Payouts">
        <div class="dock-icon">
            <i class="ri-send-plane-line"></i>
        </div>
        <span class="dock-label">Saques</span>
    </a>

    <!-- Disputes/Refunds -->
    <a href="{{ route('refunds') ?? '#' }}" class="dock-item {{ request()->routeIs('refunds') ? 'active' : '' }}" title="Refunds">
        <div class="dock-icon">
            <i class="ri-refund-2-line"></i>
        </div>
        <span class="dock-label">Reembolsos</span>
    </a>

    <!-- Separator -->
    <div class="dock-separator"></div>

    <!-- API Keys -->
    <a href="{{ route('integracoes') ?? '#' }}" class="dock-item {{ request()->routeIs('integracoes') ? 'active' : '' }}" title="Integrations">
        <div class="dock-icon">
            <i class="ri-plug-2-line"></i>
        </div>
        <span class="dock-label">Integrações</span>
    </a>

    <!-- Settings -->
    <a href="{{ route('settings.index') ?? route('settings.account') ?? '#' }}" class="dock-item {{ request()->routeIs('settings.*') ? 'active' : '' }}" title="Settings">
        <div class="dock-icon">
            <i class="ri-settings-4-line"></i>
        </div>
        <span class="dock-label">Configurações</span>
    </a>

    <!-- Profile -->
    <a href="{{ route('profile.edit') ?? '#' }}" class="dock-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" title="Profile">
        <div class="dock-icon">
            <i class="ri-user-circle-line"></i>
        </div>
        <span class="dock-label">Perfil</span>
    </a>

    <!-- Logout -->
    <form method="POST" action="{{ route('logout') }}" class="inline dock-logout-form">
        @csrf
        <button type="submit" class="dock-item" title="Logout">
            <div class="dock-icon">
                <i class="ri-logout-circle-line"></i>
            </div>
            <span class="dock-label">Sair</span>
        </button>
    </form>
</div>

<script>
    // Ensure Remix Icons are loaded
    if (!document.querySelector('link[href*="remixicon"]')) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/remixicon@3.7.0/fonts/remixicon.css';
        document.head.appendChild(link);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const dockItems = document.querySelectorAll('.dock-item');
        
        // Animate on load
        dockItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.animation = `fadeInDown 0.5s ease ${index * 0.05}s forwards`;
        });

        // Handle logout confirmation
        const logoutForm = document.querySelector('.dock-logout-form');
        if (logoutForm) {
            const logoutButton = logoutForm.querySelector('button');
            logoutButton.addEventListener('click', function(e) {
                if (!confirm('Tem certeza que deseja sair?')) {
                    e.preventDefault();
                }
            });
        }
    });
</script>

<style>
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dock-logout-form {
        display: contents;
    }

    .dock-logout-form button {
        border: none;
        background: none;
        padding: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dock-logout-form button:focus {
        outline: none;
    }

    .dock-icon i {
        font-size: 1.8rem;
        color: inherit;
        line-height: 1;
    }

    .dock-item:hover .dock-icon i {
        color: #D4AF37;
        text-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
    }

    .dock-item.active .dock-icon i {
        color: #D4AF37;
    }
</style>

