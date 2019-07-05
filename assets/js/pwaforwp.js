jQuery(document).ready(function($){
    
  $('#pwaforwp_loading_div').hide();
  $('#pwaforwp_loading_icon').hide();   
  
  $(window).on('beforeunload', function() {
     $('#pwaforwp_loading_div').show();
     $('#pwaforwp_loading_icon').show();   
  });
    
});
