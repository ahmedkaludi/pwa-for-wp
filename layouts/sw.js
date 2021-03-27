const CACHE_VERSION = '{{CACHE_VERSION}}';

const BASE_CACHE_FILES = [
    {{PRE_CACHE_URLS}}
];

const OFFLINE_CACHE_FILES = [
     '{{OFFLINE_PAGE}}',
];

const NOT_FOUND_CACHE_FILES = [
    '{{404_PAGE}}',
];

const OFFLINE_PAGE = '{{OFFLINE_PAGE}}';
const NOT_FOUND_PAGE = '{{404_PAGE}}';

const CACHE_VERSIONS = {
    content: 'content-v' + CACHE_VERSION,
    notFound: '404-v' + CACHE_VERSION,
    offline: 'offline-v' + CACHE_VERSION,
};

// Define MAX_TTL's in SECONDS for specific file extensions
const MAX_TTL = {
    '/': {{HTML_CACHE_TIME}},
    html: {{HTML_CACHE_TIME}},
    json: {{CSS_CACHE_TIME}},
    js: {{CSS_CACHE_TIME}},
    css: {{CSS_CACHE_TIME}},
    png: {{CSS_CACHE_TIME}},
    jpg: {{CSS_CACHE_TIME}},
};

const CACHE_STRATEGY = {
    default: '{{DEFAULT_CACHE_STRATEGY}}',
    css_js: '{{CSS_JS_CACHE_STRATEGY}}',
    images: '{{IMAGES_CACHE_STRATEGY}}',
    fonts: '{{FONTS_CACHE_STRATEGY}}',
};

const CACHE_BLACKLIST =  [
//    (str) => {
//        return !str.includes('/wp-admin/') || !str.startsWith('{{SITE_URL}}/wp-admin/');
//    },
];
const neverCacheUrls = [/\/wp-admin/,/\/wp-login/,/preview=true/,/\/cart/,/ajax/,/login/,{{EXCLUDE_FROM_CACHE}}];


const SUPPORTED_METHODS = [
    'GET',
];
// Check if current url is in the neverCacheUrls list
function pwaForWpcheckNeverCacheList(url) {
    if ( this.match(url) ) {
        return false;
    }
    return true;
}
/**
 * pwaForWpisBlackListed
 * @param {string} url
 * @returns {boolean}
 */
function pwaForWpisBlackListed(url) {
    return (CACHE_BLACKLIST.length > 0) ? !CACHE_BLACKLIST.filter((rule) => {
        if(typeof rule === 'function') {
            return !rule(url);
        } else {
            return false;
        }
    }).length : false
}

/**
 * pwaForWpgetFileExtension
 * @param {string} url
 * @returns {string}
 */
function pwaForWpgetFileExtension(url) {
    
    if (typeof url === 'string') {
     
        let split_two = url.split('?');
        let split_url = split_two[0];

        let extension = split_url.split('.').reverse()[0].split('?')[0];		
        return (extension.endsWith('/')) ? '/' : extension;
        
    }else{
        return null;
    }            
}
/**
 * pwaForWpgetTTL
 * @param {string} url
 */
function pwaForWpgetTTL(url) {
    if (typeof url === 'string') {
        let extension = pwaForWpgetFileExtension(url);
        if (typeof MAX_TTL[extension] === 'number') {
            return MAX_TTL[extension];
        } else {
            return MAX_TTL["/"];
        }
    } else {
        return MAX_TTL["/"];
    }
}

/**
 * pwaForWpinstallServiceWorker
 * @returns {Promise}
 */
function pwaForWpinstallServiceWorker() {
    return Promise.all(
        [
            caches.open(CACHE_VERSIONS.content)
                .then(
                    (cache) => {
                        
                        if(BASE_CACHE_FILES.length >0){
                        
                            for (var i = 0; i < BASE_CACHE_FILES.length; i++) {
                            
                             pwaForWpprecacheUrl(BASE_CACHE_FILES[i]) 
                       
                            }
                            
                        }
                        
                        //return cache.addAll(BASE_CACHE_FILES);
                    }
                ),
            caches.open(CACHE_VERSIONS.offline)
                .then(
                    (cache) => {
                        return cache.addAll(OFFLINE_CACHE_FILES);
                    }
                ),
            caches.open(CACHE_VERSIONS.notFound)
                .then(
                    (cache) => {
                        return cache.addAll(NOT_FOUND_CACHE_FILES);
                    }
                )
        ]
    )
        .then(() => {
            return self.skipWaiting();
        });
}

/**
 * pwaForWpcleanupLegacyCache
 * @returns {Promise}
 */
function pwaForWpcleanupLegacyCache() {

    let currentCaches = Object.keys(CACHE_VERSIONS)
        .map(
            (key) => {
                return CACHE_VERSIONS[key];
            }
        );

    return new Promise(
        (resolve, reject) => {

            caches.keys()
                .then(
                    (keys) => {
                        return legacyKeys = keys.filter(
                            (key) => {
                                return !~currentCaches.indexOf(key);
                            }
                        );
                    }
                )
                .then(
                    (legacy) => {
                        if (legacy.length) {
                            Promise.all(
                                legacy.map(
                                    (legacyKey) => {
                                        return caches.delete(legacyKey)
                                    }
                                )
                            )
                                .then(
                                    () => {
                                        resolve()
                                    }
                                )
                                .catch(
                                    (err) => {
                                        reject(err);
                                    }
                                );
                        } else {
                            resolve();
                        }
                    }
                )
                .catch(
                    () => {
                        reject();
                    }
                );

        }
    );
}

function pwaForWpprecacheUrl(url) {

    if(!pwaForWpisBlackListed(url)) {
        caches.open(CACHE_VERSIONS.content)
            .then((cache) => {
                cache.match(url)
                    .then((response) => {
                        if(!response) {
                            return fetch(url)
                        } else {
                            // already in cache, nothing to do.
                            return null
                        }
                    })
                    .then((response) => {
                        if(response) {						                                                     
                             fetch(url).then(dataWrappedByPromise => dataWrappedByPromise.text())									
                             .then(data => {
							 if(data){
                                const regex = {{REGEX}};
                                let m;
                                while ((m = regex.exec(data)) !== null) {
                                    if (m.index === regex.lastIndex) {
                                            regex.lastIndex++;
                                    }
                                    m.forEach((match, groupIndex) => {
                                            if(groupIndex == 1){
                                                if(new URL(match).origin == location.origin){
                                                    fetch(match).
                                                            then((imagedata) => {
                                                                    //console.log(imagedata);
                                                                    cache.put(match, imagedata.clone());

                                                    });
                                                }
                                            }
                                    });
                                }


                            }


					});
											                                                                                						
                            return cache.put(url, response.clone());
                        } else {
                            return null;
                        }
                    });
            })
    }
}

var fetchRengeData = function(event){
    var pos = Number(/^bytes\=(\d+)\-$/g.exec(event.request.headers.get('range'))[1]);
            console.log('Range request for', event.request.url, ', starting position:', pos);
            event.respondWith(
              caches.open(CACHE_VERSIONS.content)
              .then(function(cache) {
                return cache.match(event.request.url);
              }).then(function(res) {
                if (!res) {
                  return fetch(event.request)
                  .then(res => {
                    return res.arrayBuffer();
                  });
                }
                return res.arrayBuffer();
              }).then(function(ab) {
                return new Response(
                  ab.slice(pos),
                  {
                    status: 206,
                    statusText: 'Partial Content',
                    headers: [
                      // ['Content-Type', 'video/webm'],
                      ['Content-Range', 'bytes ' + pos + '-' +
                        (ab.byteLength - 1) + '/' + ab.byteLength]]
                  });
              }));
}

let cachingStrategy = {
        notGetMethods: function(event){
            // If non-GET request, try the network first, fall back to the offline page
            if (event.request.method !== 'GET') {
                event.respondWith(
                    fetch(event.request)
                        .catch(error => {
                            return caches.match(offlinePage);
                        })
                );
                return false;
            }
        },

        fetchFromCache: function(event){
           /* return new Promise(
                            (resolve) => {*/
                return caches.open(CACHE_VERSIONS.content)
                    .then(
                        (cache) => {

                            return cache.match(event.request)
                                .then(
                                    (response) => {

                                        if (response) {

                                            let headers = response.headers.entries();
                                            let date = null;

                                            for (let pair of headers) {
                                                if (pair[0] === 'date') {
                                                    date = new Date(pair[1]);
                                                }
                                            }

                                            if (date) {
                                                let age = parseInt((new Date().getTime() - date.getTime())/1000);
                                                let ttl = pwaForWpgetTTL(event.request.url);

                                                if (age > ttl) {

                                                    return new Promise(
                                                        (resolve) => {

                                                            return fetch(event.request.clone())
                                                                .then(
                                                                    (updatedResponse) => {
                                                                        if (updatedResponse) {
                                                                            cache.put(event.request, updatedResponse.clone());
                                                                            resolve(updatedResponse);
                                                                        } else {
                                                                            resolve(response)
                                                                        }
                                                                    }
                                                                )
                                                                .catch(
                                                                    () => {
                                                                        resolve(response);
                                                                    }
                                                                );

                                                        }
                                                    )
                                                        .catch(
                                                            (err) => {
                                                                return response;
                                                            }
                                                        );
                                                } else {
                                                    return response;
                                                }

                                            } else {
                                                return response;
                                            }

                                        } else {
                                            return null;
                                        }
                                    }
                                )
                                .then(
                                    (response) => {
                                        if (response) {
                                            return response;
                                        } else {
                                            return fetch(event.request.clone())
                                                .then(
                                                    (response) => {

                                                        if(response.status < 300) {
                                                            if (~SUPPORTED_METHODS.indexOf(event.request.method) && !pwaForWpisBlackListed(event.request.url)) {
                                                                cache.put(event.request, response.clone());
                                                            }
                                                                return response;
                                                        } else {
                                                            return caches.open(CACHE_VERSIONS.notFound).then((cache) => {
                                                                return cache.match(NOT_FOUND_PAGE);
                                                            })
                                                        }
                                                    }
                                                )
                                                .then((response) => {
                                                    if(response) {
                                                        return response;
                                                    }
                                                })
                                                .catch(
                                                    () => {

                                                        return caches.open(CACHE_VERSIONS.offline)
                                                            .then(
                                                                (offlineCache) => {
                                                                    return offlineCache.match(OFFLINE_PAGE)
                                                                }
                                                            )

                                                    }
                                                );
                                        }
                                    }
                                )
                                .catch(
                                    (error) => {
                                        console.error('  Error in fetch handler:', error);
                                        throw error;
                                    }
                                );
                        }
                    )
            /*})*/

        },
        fetchnetwork: function(event){
            return caches.open(CACHE_VERSIONS.content)
                    .then(
                        (cache) => {
                           return fetch(event.request.clone()).then(function (response) {

                                if(response.status < 300) {
                                    if (~SUPPORTED_METHODS.indexOf(event.request.method) && !pwaForWpisBlackListed(event.request.url)) {
                                        cache.put(event.request, response.clone());
                                    }
                                        return response;
                                }else if(response.status==404){
                                    return cachingStrategy.Notfoundpage();
                                } else if( cache.match(event.request) ){
                                    return cache.match(event.request);
                                }else {
                                    return cachingStrategy.Offlinepage();
                                }
                              }).catch(
                                   (err) => {
                                        return cache.match(event.request)
                                    }
                              ).catch(
                                (err) => {
                                        return cachingStrategy.Offlinepage();
                                    }
                              )
                        }
                    ).catch(
                           (err) => {
                                return cachingStrategy.Offlinepage();
                            }
                      )
        },
        addCache: function(event,updatedResponse){
            cache.put(event.request, updatedResponse.clone());
             resolve(updatedResponse);
        },
        Offlinepage: function(){
            return caches.open(CACHE_VERSIONS.offline).then((cache) => {
                return cache.match(OFFLINE_PAGE);
            })
        },
        Notfoundpage: function(){
            return caches.open(CACHE_VERSIONS.notFound).then((cache) => {
                return cache.match(NOT_FOUND_PAGE);
            })
        },
        /*Strategies*/
        networkOnlyStrategy: function(event){
            return caches.open(CACHE_VERSIONS.content)
                    .then(
                        (cache) => {
                           return fetch(event.request.clone()).then(function (response) {
                                if(response.status < 300) {
                                    if (~SUPPORTED_METHODS.indexOf(event.request.method) && !pwaForWpisBlackListed(event.request.url)) {
                                        cache.put(event.request, response.clone());
                                    }
                                    return response;
                                }else if(response.status==404){
                                    return cachingStrategy.Notfoundpage();
                                } else if(cache.match(event.request)){
                                    return cache.match(event.request)
                                } else {
                                    return cachingStrategy.Offlinepage();
                                }
                              }).catch(
                                (err) => {
                                        return cachingStrategy.Offlinepage();
                                    }
                              )
                        }
                    ).catch(
                        (err) => {
                           return cachingStrategy.Offlinepage()
                        }
                    );
        },
        cacheFirstStrategy: function(events){
            return cachingStrategy.fetchFromCache(events).catch(
                        (err) => {
                           return cachingStrategy.Offlinepage()
                        }
                    );
        },
        NeworkFirstStrategy: function(events){
            return cachingStrategy.fetchnetwork(events).catch(
                        (err) => {
                            return cachingStrategy.fetchFromCache(events)
                        }
                    ).catch(
                        (err) => {
                           return cachingStrategy.Offlinepage()
                        }
                    );
        }


}


self.addEventListener(
    'install', event => {
        event.waitUntil(
            Promise.all([
                pwaForWpinstallServiceWorker(),
                self.skipWaiting(),
            ])
        );
    }
);

// The activate handler takes care of cleaning up old caches.
self.addEventListener(
    'activate', event => {
        event.waitUntil(
            Promise.all(
                [
                    pwaForWpcleanupLegacyCache(),
                    self.clients.claim(),
                    self.skipWaiting(),
                ]
            )
                .catch(
                    (err) => {
                        self.skipWaiting();
                    }
                )
        );
    }
);
self.addEventListener('online', event => {
    if (navigator.onLine && navigator.standalone === true) {
        isReachable(event.request.url).then(function(online) {
          if (online) {
            //handle online status
            caches.delete(event.request.url);
            console.log('online');
          } else {
            console.log('no connectivity');
          }
        });
    } else {
        //handle offline status
        console.log('offline');
    }
});
function isReachable(url) {
  /**
   * Note: fetch() still "succeeds" for 404s on subdirectories,
   * which is ok when only testing for domain reachability.
   */
  return fetch(url, { method: 'HEAD', mode: 'no-cors' })
    .then(function(resp) {
      return resp && (resp.ok || resp.type === 'opaque');
    })
    .catch(function(err) {
      console.warn('[conn test failure]:', err);
    });
}

self.addEventListener(
    'fetch', event => {
        // Return if the current request url is in the never cache list
        if ( ! neverCacheUrls.every(pwaForWpcheckNeverCacheList, event.request.url) ) {
           //console.log( 'PWA ServiceWorker: URL exists in excluded list of cache.' + event.request.url);
          return;
        }
        if(! neverCacheUrls.every(pwaForWpcheckNeverCacheList, event.request.referrer) ){
           //console.log( 'PWA ServiceWorker: Ref-URL exists in excluded list of cache.' + event.request.referrer);
            return;
        }
        if(pwaForWpisBlackListed(event.request.url)){
            return;   
        }
        
        // Return if request url protocal isn't http or https
        if ( ! event.request.url.match(/^(http|https):\/\//i) )
            return;
        if ( event.request.referrer.match(/^(wp-admin):\/\//i) )
            return;
                       
        {{EXTERNAL_LINKS}}


        if (event.request.headers.get('range')) {
            fetchRengeData(event);
        } else {
            if(event.request.method !== 'GET' ){
                event.respondWith(
                    fetch(event.request)
                        .catch(error => {
                            {{fallbackPostRequest}}
                        })
                );
                return false;
            }
            const destination = event.request.destination;
            switch (destination) {
                case 'style':
                case 'script':
                  cachingStrategyType = CACHE_STRATEGY.css_js;
                  break;
                case 'document':
                  cachingStrategyType = CACHE_STRATEGY.default
                  break;
                case 'image': 
                    cachingStrategyType = CACHE_STRATEGY.images;
                  break;
                case 'font': 
                    cachingStrategyType = CACHE_STRATEGY.fonts;
                break;
                // All `XMLHttpRequest` or `fetch()` calls where
                // `Request.destination` is the empty string default value
                default: 
                  cachingStrategyType = CACHE_STRATEGY.default
            }
            var cache = null;
            switch(cachingStrategyType){
                case "networkFirst":
                   cache = cachingStrategy.NeworkFirstStrategy(event)
                break;
                case "networkOnly":
                   cache = cachingStrategy.networkOnlyStrategy(event)
                break;
                //break;
                case "cacheFirst":
                case "staleWhileRevalidate": 
                default:
                   cache = cachingStrategy.cacheFirstStrategy(event)
                break;
            }
            event.respondWith(cache);
        
        }

    }
);


self.addEventListener('message', (event) => {

    if(
        typeof event.data === 'object' &&
        typeof event.data.action === 'string'
    ) {
        switch(event.data.action) {
            case 'cache' :               
                pwaForWpprecacheUrl(event.data.url);
                break;
            {{formMessageData}}
            default :
                console.log('Unknown action: ' + event.data.action);
                break;
        }
    }

});

{{OFFLINE_GOOGLE}}
{{FIREBASEJS}}
