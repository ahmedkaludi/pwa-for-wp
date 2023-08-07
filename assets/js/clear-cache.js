if ('caches' in window) {
    caches.keys()
    .then(function(keyList) {
        return Promise.all(keyList.map(function(key) {
            return caches.delete(key);
        }));
    })
}