<script>
    // Service Worker Registration with PWA Install Prompt
    if ('serviceWorker' in navigator) {
        let deferredPrompt = null;
        
        // Register service worker
        navigator.serviceWorker.register('/sw.js', {
            scope: '/'
        }).then((registration) => {
            console.log('Service Worker registered:', registration.scope);
            
            // Check for updates periodically
            setInterval(() => {
                registration.update().catch(console.error);
            }, 60 * 60 * 1000); // Every hour
            
            // Listen for controller change (new SW activated)
            let refreshing = false;
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                if (refreshing) return;
                refreshing = true;
                window.location.reload();
            });
        }).catch((error) => {
            console.warn('Service Worker registration failed:', error);
        });
        
        // PWA Install Prompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.deferredInstallPrompt = e;
            
            // Show install button after user engagement
            setTimeout(() => {
                if (!window.installPromptShown) {
                    showInstallPrompt();
                }
            }, 30000); // Show after 30 seconds
        });
        
        function showInstallPrompt() {
            if (window.installPromptShown) return;
            
            const banner = document.createElement('div');
            banner.id = 'pwa-install-banner';
            banner.style.cssText = `
                position: fixed;
                bottom: 1rem;
                left: 1rem;
                right: 1rem;
                max-width: 400px;
                margin: 0 auto;
                background: var(--color-surface, #fff);
                border: 1px solid var(--color-border, #e4e7ec);
                border-radius: 12px;
                padding: 1rem;
                box-shadow: 0 10px 40px rgba(13, 40, 25, 0.15);
                z-index: 1000;
                animation: slideUp 0.3s ease-out;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            `;
            
            banner.innerHTML = `
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-primary, #1F7A45); flex-shrink: 0;">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v14"/>
                </svg>
                <div style="flex: 1;">
                    <p style="font-weight: 600; font-size: 0.875rem; color: var(--color-text, #1D2939); margin: 0 0 0.25rem;">Install SolarShare</p>
                    <p style="font-size: 0.75rem; color: var(--color-text-muted, #667085); margin: 0;">Add to home screen for offline access</p>
                </div>
                <button id="pwa-install-btn" style="
                    background: var(--color-primary, #1F7A45);
                    color: white;
                    border: none;
                    border-radius: 8px;
                    padding: 0.5rem 1rem;
                    font-size: 0.875rem;
                    font-weight: 600;
                    cursor: pointer;
                ">Install</button>
                <button id="pwa-dismiss-btn" style="
                    background: none;
                    border: none;
                    color: var(--color-text-muted, #667085);
                    cursor: pointer;
                    padding: 0.25rem;
                ">×</button>
            `;
            
            document.body.appendChild(banner);
            
            document.getElementById('pwa-install-btn').addEventListener('click', async () => {
                const prompt = window.deferredInstallPrompt;
                if (prompt) {
                    prompt.prompt();
                    const { outcome } = await prompt.userChoice;
                    if (outcome === 'accepted') {
                        console.log('PWA install accepted');
                    }
                    window.deferredInstallPrompt = null;
                }
                document.getElementById('pwa-install-banner').remove();
                window.installPromptShown = true;
            });
            
            document.getElementById('pwa-dismiss-btn').addEventListener('click', () => {
                document.getElementById('pwa-install-banner').remove();
                window.installPromptShown = true;
            });
            
            window.installPromptShown = true;
        }
        
        // Handle appinstalled event
        window.addEventListener('appinstalled', () => {
            console.log('PWA installed');
            const banner = document.getElementById('pwa-install-banner');
            if (banner) banner.remove();
            window.deferredInstallPrompt = null;
            window.installPromptShown = true;
        });
        
        // Listen for controller change (SW update)
        let refreshing = false;
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            if (window.refreshing) return;
            window.refreshing = true;
            window.location.reload();
        });
        
        // Listen for SW messages
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'CACHE_STATUS') {
                console.log('Cache status:', event.data.status);
            }
        });
    }
    
    // Offline/Online detection
    window.addEventListener('online', () => {
        document.body.classList.remove('offline');
        document.body.classList.add('online');
        
        // Show online notification
        showToast('You\'re back online!', 'success');
        
        // Sync pending operations
        if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({
                type: 'SYNC_PENDING'
            });
        }
    });
    
    window.addEventListener('offline', () => {
        document.body.classList.remove('online');
        document.body.classList.add('offline');
        
        showToast('You\'re offline. Some features may be limited.', 'warning');
    });
    
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            z-index: 9999;
            animation: slideUp 0.3s ease-out;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        `;
        
        const colors = {
            success: { bg: '#027A48', text: '#fff' },
            warning: { bg: '#B54708', text: '#fff' },
            error: { bg: '#B42318', text: '#fff' },
            info: { bg: '#1F7A45', text: '#fff' }
        };
        
        const style = colors[type] || colors.info;
        toast.style.background = style.bg;
        toast.style.color = style.text;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideDown 0.3s ease-in forwards';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }
    
    // Add CSS for toast animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideUp {
            from { opacity: 0; transform: translateX(-50%) translateY(20px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }
        @keyframes slideDown {
            from { opacity: 1; transform: translateX(-50%) translateY(0); }
            to { opacity: 0; transform: translateX(-50%) translateY(20px); }
        }
    `;
    document.head.appendChild(style);
</script>