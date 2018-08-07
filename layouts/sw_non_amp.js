
                                var swsource = "pwa-sw.js";
			         if("serviceWorker" in navigator) {
//			                navigator.serviceWorker.getRegistrations().then(function(registrations) {
//			                 for(let registration of registrations) {
//			                  registration.unregister();
//			                } });
			                navigator.serviceWorker.register(swsource).then(function(reg){
			                    console.log('Congratulations!!Service Worker Registered ServiceWorker scope: ', reg.scope);
			                }).catch(function(err) {
			                    console.log('ServiceWorker registration failed: ', err);
			                });			               
			                let deferredPrompt;
			                window.addEventListener('beforeinstallprompt', (e) => {
							  e.preventDefault();
							  deferredPrompt = e;
							  // Update UI notify the user they can add to home screen
							  //e.prompt();
							  btnAdd.style.display = 'block';
                                                          btnAdd.addEventListener('click', (e) => {
							  // hide our user interface that shows our A2HS button
							  btnAdd.style.display = 'none';
							  // Show the prompt
							  deferredPrompt.prompt();
							  // Wait for the user to respond to the prompt
							  deferredPrompt.userChoice
							    .then((choiceResult) => {
							      if (choiceResult.outcome === 'accepted') {
							        console.log('User accepted the prompt');
							      } else {
							        console.log('User dismissed the prompt');
							      }
							      deferredPrompt = null;
							    });
							});
							});			              
							window.addEventListener('appinstalled', (evt) => {
							  app.logEvent('APP not installed', 'installed');
							});
			                             }                 