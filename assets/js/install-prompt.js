// Install prompt
(() => {
    let deferredPrompt;
    let installBtn;

    function createInstallButton() {
        const installPrompt = document.createElement('div');
        installPrompt.className = 'position-fixed bottom-0 end-0 m-4';
        installPrompt.style.zIndex = '9999'; // Increased z-index to ensure visibility
        installPrompt.innerHTML = `
            <button id="installBtn" class="btn btn-primary d-none shadow-lg">
                <i class="bi bi-download me-2"></i>Install SmartVote
            </button>
        `;
    document.body.appendChild(installPrompt);
    const btn = document.getElementById('installBtn');
    
    // Make sure button is properly styled
    if (btn) {
        btn.style.display = 'none'; // Start hidden
        btn.style.alignItems = 'center';
        btn.style.padding = '0.75rem 1.5rem';
        btn.style.fontSize = '1rem';
        btn.style.fontWeight = '500';
        btn.style.borderRadius = '0.5rem';
        btn.style.transition = 'all 0.3s ease';
    }
    return btn;
}

async function handleInstallClick() {
    if (!deferredPrompt) {
        return;
    }

    // Hide the button
    installBtn.classList.add('d-none');
    
    try {
        // Show the install prompt
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        
        if (outcome === 'accepted') {
            showInstallSuccess();
        }
    } catch (error) {
        console.error('Install prompt error:', error);
        // Show error message to user
        const errorToast = showToast('Installation Error', 'Failed to install the app. Please try again.', 'error');
    } finally {
        deferredPrompt = null;
    }
}

// Initialize on page load
window.addEventListener('load', () => {
    // Check if app is already installed
    if (window.matchMedia('(display-mode: standalone)').matches) {
        return;
    }
    
    // Create the install button
    installBtn = createInstallButton();
    
    // Add click handler
    installBtn.addEventListener('click', handleInstallClick);
});

// Handle install prompt event
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('beforeinstallprompt event fired');
    e.preventDefault();
    deferredPrompt = e;
    
    // Show the install button if it exists
    if (installBtn) {
        // Remove d-none and add proper display classes
        installBtn.style.display = 'flex';
        installBtn.classList.remove('d-none');
        installBtn.classList.add('d-flex', 'align-items-center');
        
        // Animate the button in
        installBtn.style.transform = 'translateY(100%)';
        installBtn.style.opacity = '0';
        setTimeout(() => {
            installBtn.style.transform = 'translateY(0)';
            installBtn.style.opacity = '1';
        }, 100);
    }
});

    window.addEventListener('appinstalled', () => {
        showInstallSuccess();
    });

    function showInstallSuccess() {
        const toast = document.createElement('div');
        toast.className = 'toast position-fixed bottom-0 end-0 m-4';
        toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');        toast.innerHTML = `
            <div class="toast-header bg-success text-white">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong class="me-auto">Installation Complete</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                SmartVote has been successfully installed! You can now launch it from your home screen.
            </div>
        `;
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
    }
})();
