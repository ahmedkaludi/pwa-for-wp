                                 importScripts("https://www.gstatic.com/firebasejs/6.2.4/firebase-app.js");
                                 importScripts("https://www.gstatic.com/firebasejs/6.2.4/firebase-messaging.js");
                                 
                                 var config ={{config}};
                                 if (!firebase.apps.length) {firebase.initializeApp(config);}		  		  		  
                                 const messaging = firebase.messaging();
                                 
                                 messaging.setBackgroundMessageHandler(function(payload) {  
                                 const notificationTitle = payload.data.title;
                                 const notificationOptions = {
                                                    body: payload.data.body,
                                                    icon: payload.data.icon,
                                                    badge: payload.data.budge,
                                                    image: payload.data.image,
                                                    vibrate: [100, 100, 200],
                                                    data: {
                                                        dateOfArrival: Date.now(),
                                                        primarykey: payload.data.primarykey,
                                                        url : payload.data.url
                                                      },
                                                    }
                                        return self.registration.showNotification(notificationTitle, notificationOptions); 

                                });
                                
                                self.addEventListener("notificationclose", function(e) {
                                var notification = e.notification;
                                var primarykey = notification.data.primarykey;
                                console.log("Closed notification: " + primarykey);
                                });
                                
                                self.addEventListener("notificationclick", function(e) {
                                    var notification = e.notification;
                                    var primarykey = notification.data.primarykey;
                                    var action = e.action;
                                    if (action === "close") {
                                      notification.close();
                                    } else {
                                      clients.openWindow(notification.data.url);
                                      notification.close();
                                    }
                                  });  