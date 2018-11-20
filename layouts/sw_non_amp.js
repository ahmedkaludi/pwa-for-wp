                                 var swsource = "{{swfile}}.js";                                          
                                 {{config}}                                 
			         if("serviceWorker" in navigator) {
                                     window.addEventListener('load', function() {			         		
			                navigator.serviceWorker.register(swsource).then(function(reg){                                                                                        
			                    console.log('Congratulations!!Service Worker Registered ServiceWorker scope: ', reg.scope);
                                            {{userserviceworker}}                                                                        
			                }).catch(function(err) {
			                    console.log('ServiceWorker registration failed: ', err);
			                });	
                                                                                                                        
			                let deferredPrompt;                                                                                                                                                                                                                                                                                                                        
			                window.addEventListener('beforeinstallprompt', (e) => {
							  e.preventDefault();
							  deferredPrompt = e;
							  // Update UI notify the user they can add to home screen							  
							  btnAdd.style.display = 'block';
                                                          btnAdd.addEventListener('click', (e) => {
							  // hide our user interface that shows our A2HS button
							  btnAdd.style.display = 'none';
							  // Show the prompt
                                                          addToHome();
							});
							});			              
							window.addEventListener('appinstalled', (evt) => {
							  app.logEvent('APP not installed', 'installed');
							});
                                                                                                                                                                                                                      
                                                     var lastScrollTop = 0;                                        
                                                        window.addEventListener('scroll', (evt) => {
							var st = document.documentElement.scrollTop;                                                                                                                
                                                        if (st > lastScrollTop){
                                                            if(deferredPrompt !=null){
                                                            document.getElementById("pwaforwp-add-to-home-click").style.display = "block";                                                                 
                                                            }                                              
                                                        } else {
                                                          document.getElementById("pwaforwp-add-to-home-click").style.display = "none";
                                                        }
                                                        lastScrollTop = st;  
							});                                                      
                                                     {{addtohomemanually}} 
                                                     var addtohomeBtn = document.getElementById("pwaforwp-add-to-home-click");		
                                                     addtohomeBtn.addEventListener("click", (e) => {
							addToHome();	
						     });                                                                                                          
                                                     function addToHome(){
                                                         deferredPrompt.prompt();
							  // Wait for the user to respond to the prompt
							  deferredPrompt.userChoice
							    .then((choiceResult) => {
							      if (choiceResult.outcome === 'accepted') {
							        console.log('User accepted the prompt');
                                                                document.getElementById("pwaforwp-add-to-home-click").style.display = "none";
							      } else {
							        console.log('User dismissed the prompt');
							      }
							      deferredPrompt = null;
							  });
                                                     } 
                                                     });
			                             }  
                                                     
                                                     