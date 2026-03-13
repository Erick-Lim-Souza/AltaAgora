// Service Worker super leve apenas para ativar o PWA
self.addEventListener('install', (e) => {
    self.skipWaiting();
});
self.addEventListener('fetch', (e) => {
    // Não fazemos cache agressivo aqui pois o PHP já gerencia isso
});