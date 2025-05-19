// PWA Install Prompt
(() => {
    let deferredPrompt;
    let installBtn;

    function createInstallButton() {
        const installPrompt = document.createElement('div');
        installPrompt.className = 'position-fixed bottom-0 end-0 m-3';
        installPrompt.style.zIndex = '1040'; // High z-index to ensure visibility
        installPrompt.innerHTML = `
            <button id="installBtn" class="btn btn-sm btn-primary rounded-pill shadow-sm d-flex align-items-center">
                <i class="bi bi-download me-1"></i><span class="d-none d-sm-inline">Install App</span>
            </button>
        `;
        document.body.appendChild(installPrompt);
        const btn = document.getElementById('installBtn');
        
        // Make sure button is properly styled
        if (btn) {
            btn.style.display = 'none'; // Start hidden
            btn.style.transition = 'all 0.3s ease';
        }
        return btn;
    }

    async function handleInstallClick() {
        if (!deferredPrompt) {
            return;
        }

        // Hide the button
        installBtn.style.display = 'none';
        
        try {
            // Show the install prompt
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                showInstallSuccess();
            }
        } catch (error) {
            console.error('Install prompt error:', error);
            // Show error message using a toast notification if available
            if (typeof showToast === 'function') {
                showToast('Error', 'Failed to install app. Please try again.', 'danger');
            }
        } finally {
            deferredPrompt = null;
        }
    }

    function showInstallSuccess() {
        // Check if Bootstrap is available for toast
        if (typeof bootstrap !== 'undefined') {
            // Create toast container if it doesn't exist
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                toastContainer.style.zIndex = '1050';
                document.body.appendChild(toastContainer);
            }
            
            // Create toast
            const toastEl = document.createElement('div');
            toastEl.className = 'toast';
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            
            // Set toast content
            toastEl.innerHTML = `
                <div class="toast-header">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    <strong class="me-auto">SmartVote Installed</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    SmartVote has been successfully installed on your device!
                </div>
            `;
            
            // Append toast to container
            toastContainer.appendChild(toastEl);
            
            // Initialize and show toast
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
            
            // Remove toast after hidden
            toastEl.addEventListener('hidden.bs.toast', () => {
                toastEl.remove();
            });
        }
    }

    // Initialize on page load
    window.addEventListener('load', () => {
        // Check if app is already installed
        if (window.matchMedia('(display-mode: standalone)').matches || 
            navigator.standalone === true) {
            return;
        }
        
        // Create the install button
        installBtn = createInstallButton();
        
        // Add click handler
        if (installBtn) {
            installBtn.addEventListener('click', handleInstallClick);
        }
    });

    // Handle install prompt event
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        
        // Show the install button if it exists
        if (installBtn) {
            installBtn.style.display = 'flex';
            // Animate the button in
            installBtn.style.transform = 'translateY(0)';
            installBtn.style.opacity = '1';
        }
    });

    // Handle installed event
    window.addEventListener('appinstalled', () => {
        // Hide button if it exists
        if (installBtn) {
            installBtn.style.display = 'none';
        }
        
        // Clear prompt
        deferredPrompt = null;
        
        // Show success message
        showInstallSuccess();
    });
})();
