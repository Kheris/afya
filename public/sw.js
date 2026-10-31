// AfyaCheck Service Worker. Toleo linatoka kwenye "sw.js?v=APP_VERSION" (angalia assets/js/pwa.js),
// kwa hiyo kubadilisha APP_VERSION kwenye config.php kunaleta update kiotomatiki.
const VERSION = new URL(self.location).searchParams.get('v') || '1';
const STATIC_CACHE = 'afyacheck-static-' + VERSION;
const PRECACHE = [
  'assets/css/style.css',
  'assets/js/theme.js',
  'assets/js/assessment.js',
  'assets/js/pwa.js',
  'assets/icons/icon-192.png',
  'offline.html',
  'emergency.php',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then((cache) =>
      Promise.all(PRECACHE.map((u) => cache.add(u).catch(() => null)))
    )
  );
  // Hatuita skipWaiting() hapa: toleo jipya linasubiri mtumiaji abonyeze "Sasisha"
  // (hivyo hatukatishi tathmini ya dalili iliyo katikati).
});

self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((k) => k.startsWith('afyacheck-') && k !== STATIC_CACHE).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  const url = new URL(req.url);
  if (req.method !== 'GET' || url.origin !== self.location.origin) return;
  if (url.pathname.includes('/api/')) return; // usiguse API kamwe

  // CSS/JS/picha: onyesha cache mara moja, sasisha nyuma (stale-while-revalidate)
  if (/\.(css|js|png|jpg|svg|ico)$/.test(url.pathname)) {
    event.respondWith(
      caches.open(STATIC_CACHE).then(async (cache) => {
        const cached = await cache.match(req);
        const network = fetch(req).then((res) => {
          if (res && res.ok) cache.put(req, res.clone());
          return res;
        }).catch(() => cached);
        return cached || network;
      })
    );
    return;
  }

  // Kurasa: network-first. Kurasa za mtumiaji (zenye data ya afya) HAZIHIFADHIWI kwa faragha;
  // ni emergency.php tu inayohifadhiwa ili ipatikane offline.
  if (req.mode === 'navigate') {
    event.respondWith(
      fetch(req).then((res) => {
        if (url.pathname.endsWith('/emergency.php') && res.ok && !res.redirected) {
          const copy = res.clone();
          caches.open(STATIC_CACHE).then((c) => c.put(req, copy)).catch(() => null);
        }
        return res;
      }).catch(async () => {
        const cache = await caches.open(STATIC_CACHE);
        if (url.pathname.endsWith('/emergency.php')) {
          const hit = await cache.match(req) || await cache.match('emergency.php');
          if (hit) return hit;
        }
        return (await cache.match('offline.html')) || Response.error();
      })
    );
  }
});
