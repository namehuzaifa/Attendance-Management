/* ---------------------------------------------------------------------------
   HRMS service worker.

   Deliberately narrow: it caches the static shell (stylesheets, scripts,
   fonts, images) and nothing else. Pages are never cached, because every one
   of them is rendered for a signed-in person — storing them would risk
   showing one employee's attendance to whoever opens the browser next, and
   would serve stale CSRF tokens.
   --------------------------------------------------------------------------- */

const VERSION = 'hrms-v1';
const SHELL = `${VERSION}-shell`;

/* Enough to render the offline notice without a network. */
const PRECACHE = [
    '/offline.html',
    '/assets/images/hrms-logo.svg',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(SHELL)
            .then((cache) => cache.addAll(PRECACHE))
            .then(() => self.skipWaiting())
            .catch(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => !key.startsWith(VERSION)).map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

/** Static files worth keeping: same-origin, versioned by the server. */
function isShellAsset(url) {
    return url.origin === self.location.origin
        && /^\/(assets|app-assets|build)\//.test(url.pathname)
        && !url.pathname.endsWith('.apk');
}

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') return;

    const url = new URL(request.url);

    // Stale-while-revalidate for the shell: instant paint, fresh next time.
    if (isShellAsset(url)) {
        event.respondWith(
            caches.open(SHELL).then(async (cache) => {
                const hit = await cache.match(request);

                const fetching = fetch(request)
                    .then((response) => {
                        if (response.ok) cache.put(request, response.clone());
                        return response;
                    })
                    .catch(() => hit);

                return hit || fetching;
            })
        );
        return;
    }

    // Page navigations go to the network. Only if that fails do we show the
    // offline notice — we never hand back a cached page.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match('/offline.html'))
        );
    }
});
