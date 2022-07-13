jQuery(document).ready(function($) {
	setTimeout(function() {
		jQuery("a").on( "click", function(event) {
			var isdownlod = event.target.attributes["download"] ? "1" : "0";
			if(isdownlod == 1){
				event.preventDefault();
				console.log(isdownlod);
				var url = jQuery(this).attr('href');
				console.log("this: ",jQuery(this));
				jQuery(this).removeAttr("href");
				jQuery(this).attr("link", url);
				var xhr = new XMLHttpRequest();
			    xhr.open('GET', url, true);
			    xhr.responseType = 'blob';
			    xhr.onload = function(e) {
			      if (this.status == 200) {
			        var myBlob = this.response;
			        console.log(myBlob);
			        var link = document.createElement('a');
			        link.href = window.URL.createObjectURL(myBlob);
			        link.download = url;
			        link.click();
			        console.log("link",link); 
			      }
			    };
			    xhr.send();
			}
		});
	}, 1000);
});