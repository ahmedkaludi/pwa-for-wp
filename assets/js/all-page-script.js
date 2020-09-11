jQuery(document).ready(function($){
      jQuery(".pwaforwp-feedback-notice-close").on("click", function(e){
          e.preventDefault();               
                jQuery.ajax({
                    type: "POST",    
                    url:ajaxurl,                    
                    dataType: "json",
                    data:{action:"pwaforwp_review_notice_close", pwaforwp_security_nonce:pwaforwp_obj.pwaforwp_security_nonce},
                    success:function(response){                       
                      if(response['status'] =='t'){
                       jQuery(".pwaforwp-feedback-notice").hide();
                      }
                    },
                    error: function(response){                    
                    console.log(response);
                    }
                    });
    
        });
        
        jQuery(".pwaforwp-feedback-notice-remindme").on("click", function(e){
                  e.preventDefault();               
                jQuery.ajax({
                    type: "POST",    
                    url:ajaxurl,                    
                    dataType: "json",
                    data:{action:"pwaforwp_review_notice_remindme", pwaforwp_security_nonce:pwaforwp_obj.pwaforwp_security_nonce},
                    success:function(response){                       
                      if(response['status'] =='t'){
                       jQuery(".pwaforwp-feedback-notice").hide();
                      }
                    },
                    error: function(response){                    
                    console.log(response);
                    }
                    });    
        });

        /* Newletters js starts here */      
        
            if(pwaforwp_obj.do_tour){
                
                var content = '<h3>You are awesome for using PWA for WP!</h3>';
                content += '<p>Do you want the latest on <b>PWA update</b> before others and some best resources on monetization in a single email? - Free just for users of PWA for WP & AMP!</p>';
                        content += '<style type="text/css">';
                        content += '.wp-pointer-buttons{ padding:0; overflow: hidden; }';
                        content += '.wp-pointer-content .button-secondary{  left: -25px;background: transparent;top: 5px; border: 0;position: relative; padding: 0; box-shadow: none;margin: 0;color: #0085ba;} .wp-pointer-content .button-primary{ display:none}  #afw_mc_embed_signup{background:#fff; clear:left; font:14px Helvetica,Arial,sans-serif; }';
                        content += '</style>';                        
                        content += '<div id="afw_mc_embed_signup">';
                        content += '<form method="POST" id="pwaforwp-subscribe-newsletter-form">';
                        content += '<div id="afw_mc_embed_signup_scroll">';
                        content += '<div class="afw-mc-field-group" style="    margin-left: 15px;    width: 195px;    float: left;">';
                        content += '<input type="text" name="name" class="form-control" placeholder="Name" hidden value="'+pwaforwp_obj.current_user_name+'" style="display:none">';
                        content += '<input type="text" value="'+pwaforwp_obj.current_user_email+'" name="email" class="form-control" placeholder="Email*"  style="      width: 180px;    padding: 6px 5px;">';
                        content += '<input type="text" name="company" class="form-control" placeholder="Website" hidden style=" display:none; width: 168px; padding: 6px 5px;" value="'+pwaforwp_obj.get_home_url+'">';
                        content += '<input type="hidden" name="ml-submit" value="1" />';
                        content += '</div>';
                        content += '<div id="mce-responses">';
                        content += '<div class="response" id="mce-error-response" style="display:none"></div>';
                        content += '<div class="response" id="mce-success-response" style="display:none"></div>';
                        content += '</div>';
                        content += '<div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_a631df13442f19caede5a5baf_c9a71edce6" tabindex="-1" value=""></div>';
                        content += '<input type="submit" value="Subscribe" name="subscribe" class="button mc-newsletter-sent" style=" background: #0085ba; border-color: #006799; padding: 0px 16px; text-shadow: 0 -1px 1px #006799,1px 0 1px #006799,0 1px 1px #006799,-1px 0 1px #006799; height: 30px; margin-top: 1px; color: #fff; box-shadow: 0 1px 0 #006799;">';
                        content += '</div>';
                        content += '</form>';
                        content += '</div>';
                
                var setup;                
                var wp_pointers_tour_opts = {
                    content:content,
                    position:{
                        edge:"top",
                        align:"left"
                    }
                };
                                
                wp_pointers_tour_opts = jQuery.extend (wp_pointers_tour_opts, {
                        buttons: function (event, t) {
                                button= jQuery ('<a id="pointer-close" class="button-secondary">' + pwaforwp_obj.button1 + '</a>');
                                button_2= jQuery ('#pointer-close.button');
                                button.bind ('click.pointer', function () {
                                        t.element.pointer ('close');
                                });
                                button_2.on('click', function() {
                                        t.element.pointer ('close');
                                } );
                                return button;
                        },
                        close: function () {

                                jQuery.post (pwaforwp_obj.ajax_url, {
                                        pointer: 'pwaforwp_subscribe_pointer',
                                        action: 'dismiss-wp-pointer'
                                });
                        },
                        show: function(event, t){
                         t.pointer.css({'left':'170px', 'top':'160px'});
                      }                                               
                });
                setup = function () {
                        jQuery(pwaforwp_obj.displayID).pointer(wp_pointers_tour_opts).pointer('open');
                         if (pwaforwp_obj.button2) {
                                jQuery ('#pointer-close').after ('<a id="pointer-primary" class="button-primary">' + pwaforwp_obj.button2+ '</a>');
                                jQuery ('#pointer-primary').click (function () {
                                        pwaforwp_obj.function_name;
                                });
                                jQuery ('#pointer-close').click (function () {
                                        jQuery.post (pwaforwp_obj.ajax_url, {
                                                pointer: 'pwaforwp_subscribe_pointer',
                                                action: 'dismiss-wp-pointer'
                                        });
                                });
                         }
                };
                if (wp_pointers_tour_opts.position && wp_pointers_tour_opts.position.defer_loading) {
                        jQuery(window).bind('load.wp-pointers', setup);
                }
                else {
                        setup ();
                }
                
            }
                
    /* Newletters js ends here */ 
    /*Newsletter submission*/
    jQuery("#pwaforwp-subscribe-newsletter-form").on('submit',function(e){
        e.preventDefault();
        var form = jQuery(this);
        var name = form.find('input[name="name"]').val();
        var email = form.find('input[name="email"]').val();
        var website = form.find('input[name="company"]').val();
        jQuery.post(pwaforwp_obj.ajax_url, {action:'pwaforwp_subscribe_newsletter',name:name, email:email,website:website},
          function(data) {
              jQuery.post (pwaforwp_obj.ajax_url, {
                      pointer: 'pwaforwp_subscribe_pointer',
                      action: 'dismiss-wp-pointer'
              }, function(){
                location.reload();
              });
          }
        );
    });
});