                                 var swsource = "{{swfile}}";                                          
                                 {{config}}                                 
			         if("serviceWorker" in navigator) {
                                     window.addEventListener('load', function() {			         		
			                navigator.serviceWorker.register(swsource, {scope: '{{home_url}}'}).then(function(reg){                                                                                        
			                    console.log('Congratulations!!Service Worker Registered ServiceWorker scope: ', reg.scope);
                                            {{userserviceworker}}                                                                        
			                }).catch(function(err) {
			                    console.log('ServiceWorker registration failed: ', err);
			                });	
                                                                                                                                                                                                                                                                              
			                let deferredPrompt;                                                                                                                                                                                                                                                                                                                        
                                                window.addEventListener('beforeinstallprompt', (e) => {
							  e.preventDefault();
							  deferredPrompt = e;
                                                          
                                                            if(deferredPrompt != null || deferredPrompt != undefined){

                                                                var a2hsviashortcode = document.getElementsByClassName("pwaforwp-add-via-class");
                                                                if(a2hsviashortcode !== null){
                                                                    for (var i = 0; i < a2hsviashortcode.length; i++) {
                                                                      a2hsviashortcode[i].style.display="block"; 
                                                                  }
                                                                }
                                                                
                                                                var a2hsviashortcode = document.getElementsByClassName("pwaforwp-sticky-banner");
                                                                if(a2hsviashortcode !== null){
                                                                    for (var i = 0; i < a2hsviashortcode.length; i++) {
                                                                      a2hsviashortcode[i].style.display="flex"; 
                                                                  }
                                                                }

                                                             }
                                                                                                                    
							});			              							
                                                                                                                                                   
                                                     {{addtohomebanner}}                                                         
                                                     {{addtohomemanually}}
                                                                                                                                                               
                                                     var a2hsviashortcode = document.getElementsByClassName("pwaforwp-add-via-class");
                                                        if(a2hsviashortcode !== null){
                                                            for (var i = 0; i < a2hsviashortcode.length; i++) {
                                                              a2hsviashortcode[i].addEventListener("click", addToHome); 
                                                          }
                                                        }
                                         
                                                     window.addEventListener('appinstalled', (evt) => {
							  
                                                          var a2hsviashortcode = document.getElementsByClassName("pwaforwp-add-via-class");
                                                                 if(a2hsviashortcode !== null){
                                                                     for (var i = 0; i < a2hsviashortcode.length; i++) {
                                                                       a2hsviashortcode[i].style.display="none"; 
                                                                   }
                                                                 }
                                                                 
                                                                 var a2hsviashortcode = document.getElementsByClassName("pwaforwp-sticky-banner");
                                                                    if(a2hsviashortcode !== null){
                                                                        for (var i = 0; i < a2hsviashortcode.length; i++) {
                                                                          a2hsviashortcode[i].style.display="none"; 
                                                                      }
                                                                    }
                                                                                                                    
                                                     });  
                                                                                                          
                                                     function addToHome(){
                                                         
                                                         deferredPrompt.prompt();							  
                                                         deferredPrompt.userChoice
                                                           .then((choiceResult) => {
                                                             if (choiceResult.outcome === "accepted") {
                                                                 
                                                               {{addtohomefunction}} 
                                                       
                                                                var a2hsviashortcode = document.getElementsByClassName("pwaforwp-add-via-class");
                                                                 if(a2hsviashortcode !== null){
                                                                     for (var i = 0; i < a2hsviashortcode.length; i++) {
                                                                       a2hsviashortcode[i].style.display="none"; 
                                                                   }
                                                                 }
                                                                 
                                                                 var a2hsviashortcode = document.getElementsByClassName("pwaforwp-sticky-banner");
                                                                    if(a2hsviashortcode !== null){
                                                                        for (var i = 0; i < a2hsviashortcode.length; i++) {
                                                                          a2hsviashortcode[i].style.display="none"; 
                                                                      }
                                                                    }
                                                                                                                                          
                                                               console.log("User accepted the prompt");

                                                             } else {
                                                               console.log("User dismissed the prompt");
                                                             }
                                                             deferredPrompt = null;
                                                         });
                                                         
                                                        }                                                                                                                                                               
                                                     });
			                             }  
                                                     
                                                     