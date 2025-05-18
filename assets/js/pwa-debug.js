// PWA Debug Tool
const checkPWAStatus = async () => {
    console.group('PWA Installation Status Check');
    
    // Check if running as standalone
    console.log('Running as standalone:', window.matchMedia('(display-mode: standalone)').matches);
    
    // Check service worker support
    console.log('Service Worker supported:', 'serviceWorker' in navigator);
    
    // Check service worker registration
    if ('serviceWorker' in navigator) {
        try {
            const registration = await navigator.serviceWorker.getRegistration();
            console.log('Service Worker registered:', !!registration);
        } catch (err) {
            console.log('Service Worker registration check failed:', err);
        }
    }
    
    // Check manifest
    const manifestLink = document.querySelector('link[rel="manifest"]');
    console.log('Manifest link present:', !!manifestLink);
    
    if (manifestLink) {
        try {
            const manifestResponse = await fetch(manifestLink.href);
            const manifest = await manifestResponse.json();
            console.log('Manifest loaded successfully:', manifest);
        } catch (err) {
            console.log('Failed to load manifest:', err);
        }
    }
    
    // Check display mode
    if (navigator.standalone || window.matchMedia('(display-mode: standalone)').matches) {
        console.log('App is installed and running in standalone mode');
    } else {
        console.log('App is running in browser mode');
    }
    
    console.groupEnd();
};

// Run the check when page loads
window.addEventListener('load', checkPWAStatus);

// Log beforeinstallprompt event
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('beforeinstallprompt event fired at:', new Date().toISOString());
    console.log('Platform:', navigator.platform);
    console.log('User Agent:', navigator.userAgent);
});
