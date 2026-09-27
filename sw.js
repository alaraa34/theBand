/* Service worker theBand
 * - Pages PHP : toujours le réseau (données fraîches, session), page hors ligne en secours
 * - Fichiers statiques du site (css, js, images, polices) : cache, mis à jour en arrière-plan
 * - POST et ressources d'autres domaines (CDN) : non touchés
 * Changer VERSION à chaque modification de ce fichier ou des fichiers précachés.
 */
const VERSION = 'theband-v1';
const PRECACHE = [
  './offline.html',
  './icons/icon-192.png',
  './icons/icon-512.png'
];
const STATIQUE = /\.(css|js|png|jpg|jpeg|gif|svg|webp|ico|woff2?|ttf)$/i;

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(VERSION).then(cache => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys()
      .then(cles => Promise.all(cles.filter(c => c !== VERSION).map(c => caches.delete(c))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  const req = event.request;
  if (req.method !== 'GET') return;                           // formulaires : pas d'interception
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;            // CDN Bootstrap, Font Awesome...

  // Navigation (pages ?ctr=...&fct=...) : réseau d'abord
  if (req.mode === 'navigate') {
    event.respondWith(fetch(req).catch(() => caches.match('./offline.html')));
    return;
  }

  // Statiques : cache immédiat + rafraîchissement en arrière-plan
  if (STATIQUE.test(url.pathname)) {
    event.respondWith(
      caches.open(VERSION).then(async cache => {
        const enCache = await cache.match(req);
        const reseau = fetch(req).then(rep => {
          if (rep.ok) cache.put(req, rep.clone());
          return rep;
        }).catch(() => enCache);
        return enCache || reseau;
      })
    );
  }
});
