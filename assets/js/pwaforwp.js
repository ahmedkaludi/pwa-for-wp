jQuery(document).ready(function($){
    
  $('#pwaforwp_loading_div').hide();
  $('#pwaforwp_loading_icon').hide();   
  $('.pwaforwp-loading-wrapper').hide();   
  
  $(window).on('beforeunload', function() {
     $('#pwaforwp_loading_div').show();
     $('#pwaforwp_loading_icon').show();   
     $('.pwaforwp-loading-wrapper').show();   
  });
        
    $(".pwaforwp_add_home_close").on("click", function(){
        $(this).parent().hide();
    });
    
    if($('.pwaforwp-add-via-class').is(':hidden')){
        $(".pwaforwp-sticky-banner").hide();
    }else{
        $(".pwaforwp-sticky-banner").show();
    }
    
});
