(function(){
	setTimeout(()=>{
		if(window.matchMedia('(display-mode: standalone)').matches || window.matchMedia('(display-mode: fullscreen)').matches || window.matchMedia('(display-mode: minimal-ui)').matches) { 
			if (!navigator.onLine) {
				var video = document.getElementsByTagName("video");
				Array.from(video).forEach(function(elm){
					console.log("Each Loop",elm);
					var src = elm.src;
					console.log(src);
					var ext = isVideo(src);
					if(ext==true){
						elm.setAttribute("src",'');
						elm.parentElement.append('<p>MP4 video is not supported in service worker app</p>');
					}
				})
	  		}
		}

		function isVideo(filename) {
		    var ext = getExtension(filename);
		    switch (ext.toLowerCase()) {
		    case 'mp4':
		        return true;
		    }
		    return false;
		}

		function getExtension(filename) {
		    var parts = filename.split('.');
		    return parts[parts.length - 1];
		}

	},1000)
})()