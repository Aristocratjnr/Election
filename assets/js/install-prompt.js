// Install prompt
const installPrompt = document.createElement('div');
installPrompt.className = 'position-fixed bottom-0 end-0 m-4';
installPrompt.style.zIndex = '1050';
installPrompt.innerHTML = `
    <button id="installBtn" class="btn btn-primary d-none d-flex align-items-center">
        <i class="bi bi-download me-2"></i>Install SmartVote
    </button>
`;
document.body.appendChild(installPrompt);

let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    const installBtn = document.getElementById('installBtn');
    installBtn.classList.remove('d-none');
    
    installBtn.addEventListener('click', async () => {
        installBtn.classList.add('d-none');
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        console.log(`User response to the install prompt: ${outcome}`);
        deferredPrompt = null;
        
        if (outcome === 'accepted') {
            showInstallSuccess();
        }
    });
});

window.addEventListener('appinstalled', () => {
    showInstallSuccess();
});

function showInstallSuccess() {
    const toast = document.createElement('div');
    toast.className = 'toast position-fixed bottom-0 end-0 m-4';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.innerHTML = `
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
