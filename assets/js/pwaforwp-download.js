(function () {
  setTimeout(() => {
    const isPWA =
      window.matchMedia('(display-mode: standalone)').matches ||
      window.matchMedia('(display-mode: fullscreen)').matches ||
      window.matchMedia('(display-mode: minimal-ui)').matches;

    if (!isPWA) return;

    document.addEventListener('click', function (event) {
      const target = event.target.closest('a'); // supports nested elements
      if (!target) return;

      const downloadAttr = target.getAttribute('download');
      const href = target.getAttribute('href');

      if (!downloadAttr || !href) return;

      event.preventDefault(); // Prevent navigation

      const xhr = new XMLHttpRequest();
      xhr.open('GET', href, true);
      xhr.responseType = 'blob';

      xhr.onload = function () {
        if (xhr.status === 200) {
          const blob = xhr.response;
          const blobUrl = URL.createObjectURL(blob);

          const tempLink = document.createElement('a');
          tempLink.href = blobUrl;
          tempLink.download = downloadAttr;
          document.body.appendChild(tempLink);
          tempLink.click();
          document.body.removeChild(tempLink);

          URL.revokeObjectURL(blobUrl); // Clean up
        } else {
          console.error(`Failed to download file. Status: ${xhr.status}`);
        }
      };

      xhr.onerror = function () {
        console.error('Download failed due to a network error.');
      };

      xhr.send();
    });
  }, 1000);
})();

/*
** Forcing rememberme option for the login. **
Otherwise user will get logged out after sometime or after reopening the PWA app.
By rememberme wordpress keeps the login session valid for 14 days (As of now we can not change that) instead of current session
*/
document.addEventListener('DOMContentLoaded', function() {
	if(pwaforwp_download_js_obj && pwaforwp_download_js_obj.hasOwnProperty('force_rememberme') && pwaforwp_download_js_obj.force_rememberme == 1)
	{
	  var rememberMeCheckbox = document.getElementById('rememberme');    // for forms extending default login form
	  var rememberMeCheckbox2 = document.querySelector('[name="rememberme"]'); // for elementor login form
	
	  if (rememberMeCheckbox) {
		  rememberMeCheckbox.checked = true;
	  }
	  
	  if(rememberMeCheckbox2){
		rememberMeCheckbox2.checked = true;
	  }
	}
  });