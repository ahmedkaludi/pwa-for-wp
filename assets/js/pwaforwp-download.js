(function(){
	setTimeout(()=>{
		document.addEventListener("click", function(event){
			if(event.target.tagName === "A"){			
			var isdownlod = event.target.attributes["download"] ? "1" : "0";
			if(isdownlod == 1){
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
			        link.download = url;
			        link.click();
			      }
			    };
			    xhr.send();
			}
			}
		})

	},1000)
})()