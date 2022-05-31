jQuery(document).ready(function($) {
	if(window.matchMedia('(display-mode: standalone)').matches) { 
		console.log('Test online offline mode');
		if (!navigator.onLine) {
		  	console.log('Test offline');
		  	jQuery('video').each(function(){
				var src = this.src;
				var ext = isVideo(src);
				if(ext==true){
					jQuery(this).attr('src','');
					jQuery(this).parent().append('<p>MP4 video is not supported in service worker app</p>');
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
});