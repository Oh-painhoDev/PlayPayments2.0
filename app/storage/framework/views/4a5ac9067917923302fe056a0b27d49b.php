<!-- MacOSDock Component -->
<?php if(auth()->guard()->check()): ?>
<div id="macos-dock-container" class="w-full flex justify-center bg-transparent shrink-0 pointer-events-none <?php echo e(request()->routeIs('dashboard') ? 'dock-on-dashboard' : ''); ?>">
    <div id="macos-dock-wrapper" class="z-[9999] flex items-end justify-center pointer-events-none">
        <div id="macos-dock" class="backdrop-blur-md pointer-events-auto flex items-end"
             style="background: rgba(45, 45, 45, 0.75); border: 1px solid rgba(255, 255, 255, 0.15); position: relative;">
            <!-- Tooltip Element -->
            <div id="dock-tooltip" class="absolute left-1/2 -translate-x-1/2 -top-10 px-3 py-1 bg-white/10 backdrop-blur-lg border border-white/20 text-white text-xs font-medium rounded-md opacity-0 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                Tooltip Text
            </div>
            <div id="macos-dock-icons-container" class="relative w-full overflow-visible">
                <!-- Icons will be rendered here via JS -->
            </div>
        </div>
    </div>
</div>

<style>
    /* Fixed positioning ensures it's always at the bottom of the viewport */
    #macos-dock-container {
        position: fixed;
        bottom: 0;
        left: 0;
        z-index: 99999;
        width: 100%;
        display: flex;
        justify-content: center;
        padding-bottom: 16px; /* Floating space from the bottom edge */
        background: transparent;
        pointer-events: none;
    }

    #macos-dock-wrapper {
        pointer-events: auto;
    }

    /* 
       Dock spacing adjustment: 
       We use padding instead of margin to allow content to flow behind 
       while remaining reachable.
    */
    .scrollable-content {
        padding-bottom: 80px !important; /* Balanced space for the dock */
    }
    
    .dock-spacer {
        height: 40px;
        width: 100%;
        display: block;
        opacity: 0;
        pointer-events: none;
    }

    /* Remove the invasive margin-bottom that was creating the gap */
    main, #main-content, .min-h-screen, .flex-1.overflow-y-auto {
        margin-bottom: 0 !important;
    }

    /* Mobile specific: Left aligned ONLY on Dashboard home, centered on others */
    @media (max-width: 768px) {
        #macos-dock-container {
            justify-content: center; /* Default for other pages */
            padding-left: 0;
        }
        
        #macos-dock-container.dock-on-dashboard {
            justify-content: flex-start !important;
            padding-left: 26px !important;
        }
    }
</style>

<script>
    (function() {
        // App config mapped to Laravel Routes with SVG Icons from Lucide/HeroIcons style (as data URIs for reliability)
        const apps = [
            { 
                id: "dashboard", 
                name: "Dashboard", 
                icon: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect width='7' height='9' x='3' y='3' rx='1'/%3E%3Crect width='7' height='5' x='14' y='3' rx='1'/%3E%3Crect width='7' height='9' x='14' y='12' rx='1'/%3E%3Crect width='7' height='5' x='3' y='16' rx='1'/%3E%3C/svg%3E", 
                url: <?php echo json_encode(route('dashboard'), 15, 512) ?> 
            },
            { 
                id: "wallet", 
                name: "Carteira", 
                icon: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M21 12V7H5a2 2 0 0 1 0-4h14v4'/%3E%3Cpath d='M3 5v14a2 2 0 0 0 2 2h16v-5'/%3E%3Cpath d='M18 12a2 2 0 0 0 0 4h4v-4Z'/%3E%3C/svg%3E", 
                url: <?php echo json_encode(route('wallet.index'), 15, 512) ?> 
            },
            { 
                id: "transactions", 
                name: "Transações", 
                icon: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2'/%3E%3Cpath d='M9 2h6'/%3E%3Cpath d='M12 11h4'/%3E%3Cpath d='M12 16h4'/%3E%3Cpath d='M8 11h.01'/%3E%3Cpath d='M8 16h.01'/%3E%3C/svg%3E", 
                url: <?php echo json_encode(route('transactions.index'), 15, 512) ?> 
            },
            { 
                id: "customers", 
                name: "Clientes", 
                icon: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2'/%3E%3Ccircle cx='9' cy='7' r='4'/%3E%3Cpath d='M22 21v-2a4 4 0 0 0-3-3.87'/%3E%3Cpath d='M16 3.13a4 4 0 0 1 0 7.75'/%3E%3C/svg%3E", 
                url: <?php echo json_encode(route('customers.index'), 15, 512) ?> 
            },
            { 
                id: "payment-links", 
                name: "Links de Pagamento", 
                icon: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71'/%3E%3Cpath d='M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71'/%3E%3C/svg%3E", 
                url: <?php echo json_encode(route('payment-links.index'), 15, 512) ?> 
            },
            { 
                id: "integracoes", 
                name: "Integrações", 
                icon: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m7.5 4.27 9 5.15'/%3E%3Cpath d='M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z'/%3E%3Cpath d='m3.3 7 8.7 5 8.7-5'/%3E%3Cpath d='M12 22V12'/%3E%3C/svg%3E", 
                url: <?php echo json_encode(route('integracoes'), 15, 512) ?> 
            },
            { 
                id: "settings", 
                name: "Configurações", 
                icon: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z'/%3E%3Ccircle cx='12' cy='12' r='3'/%3E%3C/svg%3E", 
                url: <?php echo json_encode(route('settings.index'), 15, 512) ?> 
            },
            <?php if(auth()->user() && auth()->user()->role === 'admin'): ?>
            { 
                id: "admin", 
                name: "Painel Admin", 
                icon: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z'/%3E%3C/svg%3E", 
                url: <?php echo json_encode(route('admin.dashboard'), 15, 512) ?> 
            },
            <?php endif; ?>
        ];

        // Detection Logic
        const currentPath = window.location.pathname.replace(/\/$/, "");
        let activeAppId = null;
        apps.forEach(app => {
            const appPath = new URL(app.url).pathname.replace(/\/$/, "");
            if (currentPath === appPath || (appPath !== "" && currentPath.startsWith(appPath))) {
                activeAppId = app.id;
            }
        });

        // Config from React component - Reduced values for a more compact look
        function getResponsiveConfig() {
            const smallerDimension = Math.min(window.innerWidth, window.innerHeight);
            if (smallerDimension < 480) {
              return { baseIconSize: 26, maxScale: 1.2, effectWidth: 100 };
            }
            if (smallerDimension < 768) {
              return { baseIconSize: 32, maxScale: 1.3, effectWidth: 140 };
            }
            if (smallerDimension < 1024) {
              return { baseIconSize: 40, maxScale: 1.4, effectWidth: 180 };
            }
            return {
              baseIconSize: 48,
              maxScale: 1.6,
              effectWidth: 250,
            };
        }

        let config = getResponsiveConfig();
        const minScale = 1.0;
        let mouseX = null;
        let currentScales = apps.map(() => 1.0);
        let currentPositions = [];
        let lastMouseMoveTime = 0;
        let animationFrameRef = null;

        function getBaseSpacing() {
            return Math.max(8, config.baseIconSize * 0.2);
        }

        function calculateTargetMagnification(mousePosition) {
            const { baseIconSize, effectWidth, maxScale } = config;
            const baseSpacing = getBaseSpacing();
            if (mousePosition === null) return apps.map(() => minScale);

            return apps.map((_, index) => {
                const normalIconCenter = index * (baseIconSize + baseSpacing) + baseIconSize / 2;
                const minX = mousePosition - effectWidth / 2;
                const maxX = mousePosition + effectWidth / 2;

                if (normalIconCenter < minX || normalIconCenter > maxX) return minScale;

                const theta = ((normalIconCenter - minX) / effectWidth) * 2 * Math.PI;
                const cappedTheta = Math.min(Math.max(theta, 0), 2 * Math.PI);
                const scaleFactor = (1 - Math.cos(cappedTheta)) / 2;

                return minScale + scaleFactor * (maxScale - minScale);
            });
        }

        function calculatePositions(scales) {
            let currentX = 0;
            const baseSpacing = getBaseSpacing();
            return scales.map(scale => {
                const scaledWidth = config.baseIconSize * scale;
                const centerX = currentX + scaledWidth / 2;
                currentX += scaledWidth + baseSpacing;
                return centerX;
            });
        }

        const dockWrapper = document.getElementById("macos-dock");
        const dockAreaContainer = document.getElementById("macos-dock-container");
        const containerRef = document.getElementById("macos-dock-icons-container");
        const itemNodes = [];

        function initDOM() {
            containerRef.innerHTML = '';
            itemNodes.length = 0;
            apps.forEach((app, index) => {
                const item = document.createElement("div");
                item.className = "absolute cursor-pointer flex flex-col items-center justify-center p-2 rounded-xl bg-white/5 hover:bg-white/10 transition-colors duration-200 transform-gpu";
                item.style.transformOrigin = "bottom center";
                item.style.bottom = "0px";
                item.title = app.name;
                item.tabIndex = 0;
                
                item.onclick = (e) => {
                    e.preventDefault();
                    handleAppClick(app.id, index, app.url);
                };

                const img = document.createElement("img");
                img.src = app.icon;
                img.alt = app.name;
                img.className = "object-contain w-full h-full pointer-events-none";
                item.appendChild(img);

                const dot = document.createElement("div");
                dot.className = "absolute bg-white rounded-full transition-opacity duration-300";
                dot.style.left = "50%";
                dot.style.transform = "translateX(-50%)";
                dot.style.display = (app.id === activeAppId) ? "block" : "none";
                item.appendChild(dot);
                
                // Tooltip logic
                item.onmouseenter = () => {
                    const tooltip = document.getElementById('dock-tooltip');
                    tooltip.innerText = app.name;
                    tooltip.style.opacity = '1';
                    tooltip.style.left = `${currentPositions[index] + 10}px`; // +10 for horizontal padding
                    tooltip.style.top = `-${(config.baseIconSize * currentScales[index]) + 0}px`;
                };
                
                item.onmouseleave = () => {
                    document.getElementById('dock-tooltip').style.opacity = '0';
                };

                itemNodes.push({ wrapper: item, img, dot });
                containerRef.appendChild(item);
            });
            
            // Set initial positions
            currentScales = calculateTargetMagnification(null);
            currentPositions = calculatePositions(currentScales);
            updateStyles();
        }

        function handleAppClick(appId, index, url) {
            const node = itemNodes[index];
            const bounceHeight = -15;
            
            node.wrapper.style.transition = "transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275)";
            node.wrapper.style.transform = `translateY(${bounceHeight}px)`;

            setTimeout(() => {
                node.wrapper.style.transform = "translateY(0px)";
                setTimeout(() => {
                    window.location.href = url;
                }, 100);
            }, 200);
        }

        function updateStyles() {
            const baseSpacing = getBaseSpacing();
            const padding = 10;
            
            const contentWidth = currentPositions.length > 0 
                ? Math.max(...currentPositions.map((p, i) => p + (config.baseIconSize * currentScales[i]) / 2))
                : apps.length * (config.baseIconSize + baseSpacing) - baseSpacing;

            dockWrapper.style.width = `${contentWidth + padding * 2}px`;
            dockWrapper.style.padding = `${padding}px`;
            dockWrapper.style.borderRadius = "16px";
            
            // Adjust container height to include space for bounce
            dockAreaContainer.style.minHeight = `${config.baseIconSize * 1.5 + padding * 2}px`;

            dockWrapper.style.boxShadow = `
                0 20px 25px -5px rgba(0, 0, 0, 0.5),
                0 10px 10px -5px rgba(0, 0, 0, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.1)
            `;

            containerRef.style.height = `${config.baseIconSize}px`;

            apps.forEach((app, index) => {
                const node = itemNodes[index];
                if (!node) return;
                
                const scale = currentScales[index];
                const pos = currentPositions[index] || 0;
                const scaledSize = config.baseIconSize * scale;

                node.wrapper.style.width = `${scaledSize}px`;
                node.wrapper.style.height = `${scaledSize}px`;
                node.wrapper.style.left = `${pos - scaledSize / 2}px`;
                node.wrapper.style.zIndex = Math.round(scale * 10);
                
                if (app.id === activeAppId) {
                    node.dot.style.bottom = "-6px";
                    node.dot.style.width = "4px";
                    node.dot.style.height = "4px";
                }
            });
        }

        function animate() {
            const targetScales = calculateTargetMagnification(mouseX);
            const targetPositions = calculatePositions(targetScales);
            const lerpFactor = mouseX !== null ? 0.2 : 0.12;

            let needsUpdate = false;
            currentScales = currentScales.map((s, i) => {
                const diff = targetScales[i] - s;
                if (Math.abs(diff) > 0.002) needsUpdate = true;
                return s + diff * lerpFactor;
            });

            currentPositions = currentPositions.map((p, i) => {
                const diff = targetPositions[i] - p;
                if (Math.abs(diff) > 0.1) needsUpdate = true;
                return p + diff * lerpFactor;
            });

            updateStyles();

            // Dynamic tooltip position follow
            const tooltip = document.getElementById('dock-tooltip');
            if (tooltip.style.opacity === '1') {
                const hoveredIndex = apps.findIndex(app => app.name === tooltip.innerText);
                if (hoveredIndex !== -1) {
                    tooltip.style.left = `${currentPositions[hoveredIndex] + 10}px`;
                    tooltip.style.top = `-${(config.baseIconSize * currentScales[hoveredIndex]) + 0}px`;
                }
            }

            if (needsUpdate || mouseX !== null) {
                animationFrameRef = requestAnimationFrame(animate);
            } else {
                animationFrameRef = null;
            }
        }

        window.addEventListener("mousemove", (e) => {
            const rect = dockWrapper.getBoundingClientRect();
            const padding = 12;
            
            const margin = 100;
            if (e.clientY > rect.top - margin && e.clientY < rect.bottom + margin &&
                e.clientX > rect.left - margin && e.clientX < rect.right + margin) {
                mouseX = e.clientX - rect.left - padding;
            } else {
                mouseX = null;
            }

            if (!animationFrameRef) animationFrameRef = requestAnimationFrame(animate);
        });

        window.addEventListener("resize", () => {
            config = getResponsiveConfig();
            currentScales = calculateTargetMagnification(mouseX);
            currentPositions = calculatePositions(currentScales);
            updateStyles();
        });

        initDOM();
        animationFrameRef = requestAnimationFrame(animate);

    })();
</script>
<?php endif; ?>
<?php /**PATH /home/painhodev/PlayPayments2.0/app/resources/views/components/MacosDock.blade.php ENDPATH**/ ?>