// PWA Testing Script
const testPWAFeatures = async () => {
    // Test 1: Check if navigator.serviceWorker is supported
    console.log('1. Testing Service Worker Support:', 'serviceWorker' in navigator ? '✅ Supported' : '❌ Not supported');

    // Test 2: Check if service worker is registered
    try {
        const registration = await navigator.serviceWorker.ready;
        console.log('2. Service Worker Registration:', registration ? '✅ Registered' : '❌ Not registered');
    } catch (error) {
        console.log('2. Service Worker Registration: ❌ Failed -', error);
    }

    // Test 3: Check if app is installable (has proper manifest)
    if (window.matchMedia('(display-mode: standalone)').matches) {
        console.log('3. App Installation: ✅ App is installed');
    } else {
        console.log('3. App Installation: ℹ️ App can be installed');
    }

    // Test 4: Check cache storage
    try {
        const cache = await caches.open('smartvote-v1');
        const keys = await cache.keys();
        console.log('4. Cache Storage:', keys.length > 0 ? `✅ ${keys.length} items cached` : '❌ No cached items');
    } catch (error) {
        console.log('4. Cache Storage: ❌ Failed -', error);
    }

    // Test 5: Check manifest.json accessibility
    try {
        const manifestResponse = await fetch('/Election/manifest.json');
        console.log('5. Manifest Accessibility:', manifestResponse.ok ? '✅ Accessible' : '❌ Not accessible');
    } catch (error) {
        console.log('5. Manifest Accessibility: ❌ Failed -', error);
    }
};

// Run tests when page loads
window.addEventListener('load', testPWAFeatures);
