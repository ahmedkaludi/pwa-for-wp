jQuery(document).ready(function($){
  
  var isSafari = !!navigator.userAgent.match(/Version\/[\d\.]+.*Safari/);
  var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
  if (isSafari && iOS) {
    jQuery('#pwaforwp_loading_div').show();
    jQuery('#pwaforwp_loading_icon').show();   
    jQuery('.pwaforwp-loading-wrapper').show();
    setInterval(function(){
      jQuery('#pwaforwp_loading_div').hide();
      jQuery('#pwaforwp_loading_icon').hide();   
      jQuery('.pwaforwp-loading-wrapper').hide();}, 
    1000, true);
    
  }else{
  
    jQuery('#pwaforwp_loading_div').hide();
    jQuery('#pwaforwp_loading_icon').hide();   
    jQuery('.pwaforwp-loading-wrapper').hide();   

    jQuery(window).on('beforeunload', function() {
      jQuery('#pwaforwp_loading_div').show();
      jQuery('#pwaforwp_loading_icon').show();   
      jQuery('.pwaforwp-loading-wrapper').show();   
    });
  }
        
    jQuery(".pwaforwp_add_home_close").on("click", function(){
        jQuery(this).parent().hide();
    });
    
    if(jQuery('.pwaforwp-add-via-class').is(':hidden')){
        jQuery(".pwaforwp-sticky-banner").hide();
    }else{
        jQuery(".pwaforwp-sticky-banner").show();
    }
    
});
