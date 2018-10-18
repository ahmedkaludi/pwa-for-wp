 
		     {{firebaseconfig}}                     
                     if (!firebase.apps.length) {
		    firebase.initializeApp(config);	
		    }                    		  		  		  
                    const messaging = firebase.messaging();
                    
                    messaging.requestPermission().then(function() {
                    console.log("Notification permission granted.");                                    
                    if(isTokenSentToServer()){
                        console.log('Token already saved');
                    }else{
                        getRegToken();
                    }                                   
		 
                    }).catch(function(err) {
                      console.log("Unable to get permission to notify.", err);
                    });
                
                function getRegToken(argument){
                     
                    messaging.getToken().then(function(currentToken) {
                      if (currentToken) {                      
                       saveToken(currentToken);
                       console.log(currentToken);
                        setTokenSentToServer(true);
                      } else {                       
                        console.log('No Instance ID token available. Request permission to generate one.');                       
                        setTokenSentToServer(false);
                      }
                    }).catch(function(err) {
                      console.log('An error occurred while retrieving token. ', err);                      
                      setTokenSentToServer(false);
                    });
                }
                function setTokenSentToServer(sent) {
                 window.localStorage.setItem('sentToServer', sent ? '1' : '0');
                }
                
                function isTokenSentToServer() {
                return window.localStorage.getItem('sentToServer') === '1';
                }
                
                function saveToken(currentToken){
                  var xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function() {
                      if (this.readyState == 4 && this.status == 200) {
                        console.log(this.responseText);
                      }
                    };
                    xhttp.open("POST", pwaforwp_obj.ajax_url+'?action=pwaforwp_store_token', true);
                    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhttp.send("token="+currentToken);
                }              
                 messaging.onMessage(function(payload) {
                 console.log('Message received. ', payload);
                 
                 notificationTitle = payload.data.title;
                    notificationOptions = {
                    body: payload.data.body,
                    icon: payload.data.icon
                    }
                    var notification = new Notification(notificationTitle, notificationOptions); 
                        notification.onclick = function(event) {
                        event.preventDefault();
                        window.open(payload.data.url, '_blank');
                        notification.close();
                        }
                });