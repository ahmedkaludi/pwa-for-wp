		    {{firebaseconfig}}                     
                     if (!firebase.apps.length) {
		    firebase.initializeApp(config);	
		    }                    		  		  		  
                    const messaging = firebase.messaging();
                    
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
                
                function pwaForWpgetRegToken(argument){
                     
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
                 
                 notificationTitle = payload.data.title;
                    notificationOptions = {
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
                    }
                    var notification = new Notification(notificationTitle, notificationOptions); 
                        notification.onclick = function(event) {
                        event.preventDefault();
                        window.open(payload.data.url, '_blank');
                        notification.close();
                        }
                });