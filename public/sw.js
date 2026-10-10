// SolarShare Service Worker
// Version: 2024.10.10-1
// Provides offline support with multiple caching strategies

const CACHE_NAME = 'solarshare-v2024.10.10-1';
const STATIC_CACHE = 'solarshare-static-v2024.10.10-1';
const DYNAMIC_CACHE = 'solarshare-dynamic-v2024.10.10-1';
const API_CACHE = 'solarshare-api-v2024.10.10-1';
const IMAGE_CACHE = 'solarshare-images-v2024.10.10-1';

// Assets to cache immediately on install
const STATIC_ASSETS = [
  '/',
  '/manifest.json',
  '/offline.html',
  '/icons/icon-192x192.png',
  '/icons/icon-512x512.png',
];

// Cache strategies
const CACHE_STRATEGIES = {
  // Static assets - cache first, update in background
  static: ['GET', '/build/', '/fonts/', '/icons/'],
  
  // API responses - network first, fallback to cache
  api: ['GET', '/api/'],
  
  // Images - cache first, long expiry
  images: ['GET', '/images/', '/storage/', '/uploads/'],
  
  // Pages - network first, fallback to cache
  pages: ['GET', '/catalog', '/rentals', '/chat', '/profile'],
};

// Install event - cache static assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    Promise.all([
      caches.open(STATIC_CACHE).then((cache) => {
        return cache.addAll(STATIC_ASSETS);
      }),
      caches.open(DYNAMIC_CACHE),
      caches.open(API_CACHE),
      caches.open(IMAGE_CACHE),
    ]).then(() => {
      // Force activation of new service worker
      return self.skipWaiting();
    })
  );
});

// Activate event - clean old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => {
            // Delete old cache versions
            return name.startsWith('solarshare-') && 
                   name !== STATIC_CACHE &&
                   name !== DYNAMIC_CACHE &&
                   name !== API_CACHE &&
                   name !== IMAGE_CACHE;
          })
          .map((name) => caches.delete(name))
      );
    }).then(() => {
      // Claim all clients immediately
      return self.clients.claim();
    })
  );
});

// Fetch event - apply caching strategies
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }

  // Skip non-HTTP(S) requests
  if (!url.protocol.startsWith('http')) {
    return;
  }

  // Apply caching strategy based on request
  if (isStaticAsset(request)) {
    event.respondWith(cacheFirstStrategy(request, STATIC_CACHE));
  } else if (isApiRequest(request)) {
    event.respondWith(networkFirstStrategy(request, API_CACHE));
  } else if (isImageRequest(request)) {
    event.respondWith(cacheFirstStrategy(request, IMAGE_CACHE));
  } else if (isPageRequest(request)) {
    event.respondWith(networkFirstStrategy(request, DYNAMIC_CACHE));
  } else {
    // Default: network first, fallback to cache
    event.respondWith(networkFirstStrategy(request, DYNAMIC_CACHE));
  }
});

// Cache-first strategy (for static assets, images)
async function cacheFirstStrategy(request, cacheName) {
  const cache = await caches.open(cacheName);
  const cachedResponse = await cache.match(request);

  if (cachedResponse) {
    // Update cache in background
    fetch(request).then((response) => {
      if (response.ok) {
        caches.open(cacheName).then((cache) => cache.put(request, response));
      }
    }).catch(() => {
      // Ignore background update errors
    });
    return cachedResponse;
  }

  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    // Return offline page for navigation requests
    if (request.mode === 'navigate') {
      return caches.match('/offline.html');
    }
    throw error;
  }
}

// Network-first strategy (for API, pages)
async function networkFirstStrategy(request, cacheName) {
  const cache = await caches.open(cacheName);

  try {
    const networkResponse = await fetch(request);

    if (networkResponse.ok) {
      // Cache successful responses
      cache.put(request, networkResponse.clone());
    }

    return networkResponse;
  } catch (error) {
    // Fallback to cache
    const cachedResponse = await cache.match(request);

    if (cachedResponse) {
      // Add header to indicate cached response
      const response = cachedResponse.clone();
      response.headers.set('X-Served-By', 'service-worker-cache');
      return response;
    }

    // Return offline page for navigation requests
    if (request.mode === 'navigate') {
      return caches.match('/offline.html');
    }

    // Return cached API error response for API requests
    if (request.url.includes('/api/')) {
      return new Response(
        JSON.stringify({
          error: 'Offline',
          message: 'You are currently offline. Some features may be unavailable.',
          cached: false,
        }), {
          status: 503,
          headers: { 'Content-Type': 'application/json' },
        });
      }

    throw error;
  }
}

// Stale-while-revalidate strategy (for data that can be slightly stale)
async function staleWhileRevalidate(request, cacheName) {
  const cache = await caches.open(cacheName);
  const cachedResponse = await cache.match(request);

  const fetchPromise = fetch(request).then((networkResponse) => {
    if (networkResponse.ok) {
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  });

  return cachedResponse || fetchPromise;
}

// Request type detection
function isStaticAsset(request) {
  const url = new URL(request.url);
  return url.pathname.startsWith('/build/') ||
         url.pathname.startsWith('/fonts/') ||
         url.pathname.startsWith('/icons/') ||
         url.pathname.endsWith('.js') ||
         url.pathname.endsWith('.css') ||
         url.pathname.endsWith('.woff') ||
         url.pathname.endsWith('.woff2') ||
         url.pathname.endsWith('.ttf') ||
         url.pathname.endsWith('.eot');
}

function isApiRequest(request) {
  const url = new URL(request.url);
  return url.pathname.startsWith('/api/');
}

function isImageRequest(request) {
  const url = new URL(request.url);
  return url.pathname.startsWith('/images/') ||
         url.pathname.startsWith('/storage/') ||
         url.pathname.startsWith('/uploads/') ||
         url.pathname.match(/\.(jpg|jpeg|png|gif|webp|svg|ico)$/i);
}

function isPageRequest(request) {
  const url = new URL(request.url);
  return url.pathname === '/' ||
         url.pathname.startsWith('/catalog') ||
         url.pathname.startsWith('/rentals') ||
         url.pathname.startsWith('/chat') ||
         url.pathname.startsWith('/profile') ||
         url.pathname.startsWith('/dashboard') ||
         url.pathname.startsWith('/settings');
}

// Background sync for pending operations
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-pending-operations') {
    event.waitUntil(syncPendingOperations());
  }

  if (event.tag === 'sync-pending-messages') {
    event.waitUntil(syncPendingMessages());
  }

  if (event.tag === 'sync-analytics') {
    event.waitUntil(syncAnalytics());
  }
});

// Push notifications
self.addEventListener('push', (event) => {
  if (!event.data) return;

  const data = event.data.json();
  const options = {
    body: data.body,
    icon: '/icons/icon-192x192.png',
    badge: '/icons/badge-72x72.png',
    vibrate: [100, 50, 100],
    data: {
      url: data.url || '/',
    },
    actions: data.actions || [],
    requireInteraction: data.requireInteraction || false,
    silent: data.silent || false,
  };

  event.waitUntil(
    self.registration.showNotification(data.title, options)
  );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  if (event.action) {
    // Handle action buttons
    handleNotificationAction(event.action, event.notification.data);
  } else {
    // Default click - open URL
    const url = event.notification.data?.url || '/';
    event.waitUntil(
      clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
        // Try to focus existing window
        for (const client of clients) {
          if (client.url === url && 'focus' in client) {
            return client.focus();
          }
        }
        // Open new window
        return clients.openWindow(url);
      })
    );
  }
});

// Handle notification actions
async function handleNotificationAction(action, data) {
  switch (action) {
    case 'view':
      clients.openWindow(data.url || '/');
      break;
    case 'dismiss':
      // Just close notification
      break;
    case 'reply':
      // Open chat
      clients.openWindow(data.url || '/chat');
      break;
    default:
      clients.openWindow(data.url || '/');
  }
}

// Sync pending operations
async function syncPendingOperations() {
  try {
    const db = await openDB();
    const tx = db.transaction('pendingOperations', 'readwrite');
    const store = tx.objectStore('pendingOperations');
    const operations = await store.getAll();

    for (const operation of operations) {
      try {
        const response = await fetch(operation.url, {
          method: operation.method,
          headers: operation.headers,
          body: operation.body,
        });

        if (response.ok) {
          await store.delete(operation.id);
        }
      } catch (error) {
        console.error('Failed to sync operation:', error);
      }
    }

    await tx.done;
  } catch (error) {
    console.error('Sync failed:', error);
  }
}

// Sync pending messages
async function syncPendingMessages() {
  try {
    const db = await openDB();
    const tx = db.transaction('pendingMessages', 'readwrite');
    const store = tx.objectStore('pendingMessages');
    const messages = await store.getAll();

    for (const message of messages) {
      try {
        const response = await fetch('/api/chat/messages', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(message),
        });

        if (response.ok) {
          await store.delete(message.id);
        }
      } catch (error) {
        console.error('Failed to sync message:', error);
      }
    }

    await tx.done;
  } catch (error) {
    console.error('Message sync failed:', error);
  }
}

// Sync analytics
async function syncAnalytics() {
  try {
    const db = await openDB();
    const tx = db.transaction('analytics', 'readwrite');
    const store = tx.objectStore('analytics');
    const events = await store.getAll();

    if (events.length === 0) return;

    const response = await fetch('/api/analytics/batch', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ events }),
    });

    if (response.ok) {
      await store.clear();
    }
  } catch (error) {
    console.error('Analytics sync failed:', error);
  }
}

// IndexedDB helper
function openDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('SolarSharePWA', 1);

    request.onerror = () => reject(request.error);
    request.onsuccess = () => resolve(request.result);

    request.onupgradeneeded = (event) => {
      const db = event.target.result;

      if (!db.objectStoreNames.contains('pendingOperations')) {
        db.createObjectStore('pendingOperations', { keyPath: 'id', autoIncrement: true });
      }

      if (!db.objectStoreNames.contains('pendingMessages')) {
        db.createObjectStore('pendingMessages', { keyPath: 'id', autoIncrement: true });
      }

      if (!db.objectStoreNames.contains('analytics')) {
        db.createObjectStore('analytics', { keyPath: 'id', autoIncrement: true });
      }
    };
  });
}

// Periodic background sync
self.addEventListener('periodicsync', (event) => {
  if (event.tag === 'periodic-sync') {
    event.waitUntil(
      Promise.all([
        syncPendingOperations(),
        syncPendingMessages(),
        syncAnalytics(),
      ])
    );
  }
});

// Message handling from main thread
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }

  if (event.data && event.data.type === 'GET_CACHE_STATUS') {
    getCacheStatus().then((status) => {
      event.ports[0].postMessage({ status });
    });
  }

  if (event.data && event.data.type === 'CLEAR_CACHE') {
    clearAllCaches().then(() => {
      event.ports[0].postMessage({ success: true });
    });
  }
});

// Get cache status
async function getCacheStatus() {
  const cacheNames = await caches.keys();
  const status = {};

  for (const name of cacheNames) {
    const cache = await caches.open(name);
    const keys = await cache.keys();
    status[name] = {
      count: keys.length,
      size: await estimateCacheSize(cache),
    };
  }

  return status;
}

// Estimate cache size
async function estimateCacheSize(cache) {
  const keys = await cache.keys();
  let totalSize = 0;

  for (const request of keys) {
    const response = await cache.match(request);
    if (response) {
      const blob = await response.blob();
      totalSize += blob.size;
    }
  }

  return totalSize;
}

// Clear all caches
async function clearAllCaches() {
  const cacheNames = await caches.keys();
  await Promise.all(cacheNames.map((name) => caches.delete(name)));
}

// Offline fallback for critical resources
self.addEventListener('fetch', (event) => {
  // Handle critical resources that must work offline
  if (event.request.url.includes('/manifest.json')) {
    event.respondWith(
      caches.match('/manifest.json').then((response) => {
        if (response) return response;
        return fetch(event.request);
      })
    );
  }
});

// Error handling
self.addEventListener('error', (event) => {
  console.error('Service Worker Error:', event.error);
});

self.addEventListener('unhandledrejection', (event) => {
  console.error('Service Worker Unhandled Rejection:', event.reason);
});

console.log('SolarShare Service Worker loaded');