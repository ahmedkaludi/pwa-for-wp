		    var config={ apiKey: 'sddgdfgdgd', authDomain: 'tttrytrytry', databaseURL: 'ytutrtrur', projectId: 'rutrutrurturtutyu', storageBucket: 'dtyuytutru', messagingSenderId: 'rutrueru' };                     
                     if (!firebase.apps.length) {
		                      firebase.initializeApp(config);	
		                    }                    		  		  		  
                    
                    var messaging = firebase.messaging();

                    // Wait for pwa-register-sw.js to call useServiceWorker() before triggering getToken().
                    // Firebase SDK v7 requires useServiceWorker() to run exactly once, before getToken().
                    function pwaforwpStartForegroundMessaging() {
                      if (!('serviceWorker' in navigator)) {
                        console.log('Service workers are not supported in this browser.');
                        return;
                      }
                      // pwaforwpFirebaseSwReady is set in pwa-register-sw.js and resolves after useServiceWorker().
                      var swPromise = window.pwaforwpFirebaseSwReady || navigator.serviceWorker.ready;
                      swPromise.then(function () {
                        messaging.requestPermission().then(function() {
                    console.log("Notification permission granted.");                                    
                    if(pwaForWpisTokenSentToServer()){
                        pwaForWpgetRegToken();
                        console.log('Token already saved');
                    }else{
                        pwaForWpgetRegToken();
                    }                                   
		 
                    }).catch(function(err) {
                      console.log("Unable to get permission to notify.", err);
                    });
                      }).catch(function (err) {
                        console.log('Service worker is not ready yet. ', err);
                      });
                    }

                    pwaforwpStartForegroundMessaging();
                
                function pwaForWpgetRegToken(argument){
                    // Firebase SDK v7: getToken() needs no arguments; useServiceWorker() already bound the registration.
                    messaging.getToken().then(function(currentToken) {
                      if (currentToken) {                      
                       pwaForWpsaveToken(currentToken);
                       console.log(currentToken);
                        pwaForWpsetTokenSentToServer(true);
                      } else {                       
                        console.log('No Instance ID token available. Request permission to generate one.');                       
                        pwaForWpsetTokenSentToServer(false);
                      }
                    }).catch(function(err) {
                      console.log('An error occurred while retrieving token. ', err);                      
                      pwaForWpsetTokenSentToServer(false);
                    });
                }
                function pwaForWpsetTokenSentToServer(sent) {
                 window.localStorage.setItem('sentToServer', sent ? '1' : '0');
                }
                
                function pwaForWpisTokenSentToServer() {
                return window.localStorage.getItem('sentToServer') === '1';
                }
                
                function pwaForWpsaveToken(currentToken){
                  var xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function() {
                      if (this.readyState == 4 && this.status == 200) {
                        console.log(this.responseText);
                      }
                    };
                    xhttp.open("POST", pwaforwp_obj.ajax_url+'?action=pwaforwp_store_token', true);
                    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhttp.send("token="+currentToken+'&pwaforwp_security_nonce='+pwaforwp_obj.pwaforwp_security_nonce);
                }              
                 messaging.onMessage(function(payload) {
                 console.log('Message received. ', payload);
                 
                 var notificationTitle = payload.data.title;
                    var notificationOptions = {
                    body: payload.data.body,
                    icon: payload.data.icon,
                    image: payload.data.image,
                    badge: payload.data.budge,
                    vibrate: [100, 50, 100],
                    data: {
                        dateOfArrival: Date.now(),
                        primarykey: payload.data.primarykey,
                        url : payload.data.url
                      },
                    };
                    // Browsers with a SW controller require showNotification() instead of new Notification().
                    if (navigator.serviceWorker && navigator.serviceWorker.controller) {
                        navigator.serviceWorker.ready.then(function(reg) {
                            reg.showNotification(notificationTitle, notificationOptions);
                        });
                    } else if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
                        var notification = new Notification(notificationTitle, notificationOptions);
                        notification.onclick = function(event) {
                            event.preventDefault();
                            window.open(payload.data.url, '_blank');
                            notification.close();
                        };
                    }
                });