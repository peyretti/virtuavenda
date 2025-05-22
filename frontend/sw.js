/**
 * VirtuaVenda - Service Worker
 * Para funcionalidades PWA básicas
 */

const CACHE_NAME = 'virtuavenda-v1';
const urlsToCache = [
    './',
    './assets/css/style.css',
    './assets/js/main.js',
    './assets/js/cart.js',
    './placeholder.php?w=300&h=250&text=Produto'
];

// Instalação do Service Worker
self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function(cache) {
                console.log('Cache aberto');
                // Adicionar URLs uma por uma para evitar falhas
                return Promise.all(
                    urlsToCache.map(function(url) {
                        return cache.add(url).catch(function(error) {
                            console.log('Erro ao cachear:', url, error);
                        });
                    })
                );
            })
    );
});

// Interceptar requisições
self.addEventListener('fetch', function(event) {
    // Só cachear requests GET
    if (event.request.method !== 'GET') {
        return;
    }
    
    event.respondWith(
        caches.match(event.request)
            .then(function(response) {
                // Cache hit - retorna resposta
                if (response) {
                    return response;
                }
                
                // Fazer requisição na rede
                return fetch(event.request).catch(function() {
                    // Se offline e não tem cache, retornar página básica
                    if (event.request.destination === 'document') {
                        return caches.match('./');
                    }
                });
            }
        )
    );
});

// Atualização do Service Worker
self.addEventListener('activate', function(event) {
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Removendo cache antigo:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});