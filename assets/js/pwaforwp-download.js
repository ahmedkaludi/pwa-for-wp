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