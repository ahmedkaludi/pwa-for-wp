                                 var swsource = "{{swfile}}";                                          
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
                                                          
							});			              
							window.addEventListener('appinstalled', (evt) => {
							  app.logEvent('APP not installed', 'installed');
							});
                                                                                                                                                                                                                      
                                                     {{addtohomebanner}}                                                         
                                                     {{addtohomemanually}}
                                                     {{addtohomefunction}}                                                      
                                                     });
			                             }  
                                                     
                                                     