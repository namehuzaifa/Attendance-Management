<link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/hrms-logo.svg') }}">
<link rel="alternate icon" type="image/x-icon" href="{{ asset('assets/images/icon.ico') }}">

{{-- Installable web app: iOS reads these tags, everything else the manifest. --}}
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<link rel="apple-touch-icon" href="{{ asset('assets/images/pwa/apple-touch-icon.png') }}">
<meta name="theme-color" content="#7367f0">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="HRMS">

<script>
    // Registered after load so it never competes with the first paint, and
    // guarded because a service worker needs HTTPS (localhost aside).
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker
                .register('{{ asset('sw.js') }}', { scope: '/' })
                .catch(function () { /* a portal that cannot install still works */ });
        });
    }

    // Chrome fires this instead of showing its own prompt; the app dialog
    // picks it up and offers an Install button.
    window.hrmsInstallPrompt = null;
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        window.hrmsInstallPrompt = e;
        window.dispatchEvent(new Event('hrms:installable'));
    });
</script>
