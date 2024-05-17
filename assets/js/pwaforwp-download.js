(function(){
	setTimeout(()=>{
		if( window.matchMedia('(display-mode: standalone)').matches || window.matchMedia('(display-mode: fullscreen)').matches || window.matchMedia('(display-mode: minimal-ui)').matches) { 
		document.addEventListener("click", function(event){
			if(event.target.tagName === "A"){	
			var pwaforwp_download_text = event.target.attributes["download"];		
			var pwaforwp_isdownload = pwaforwp_download_text ? "1" : "0";
			if(pwaforwp_isdownload == 1){
				event.preventDefault();
				var url = event.target.attributes["href"].value;
				event.target.removeAttribute("href");
				event.target.setAttribute("link",url);
				var xhr = new XMLHttpRequest();
			    xhr.open('GET', url, true);
			    xhr.responseType = 'blob';
			    xhr.onload = function(e) {
			      if (this.status == 200) {
			        var myBlob = this.response;
			        var link = document.createElement('a');
			        link.href = window.URL.createObjectURL(myBlob);
			        link.download = pwaforwp_download_text;
			        link.click();
			      }
			    };
			    xhr.send();
			}
			}
		})
		}
	},1000)
})()

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