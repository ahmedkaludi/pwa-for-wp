function pwaforwpGetParamByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

jQuery(document).ready(function($){
    
    /* Newletters js starts here */      
        
            if(pwaforwp_obj.do_tour){
                
                var content = '<h3>You are awesome for using PWA for WP!</h3>';
                content += '<p>Do you want the latest on <b>PWA update</b> before others and some best resources on monetization in a single email? - Free just for users of ADS!</p>';
                        content += '<style type="text/css">';
                        content += '.wp-pointer-buttons{ padding:0; overflow: hidden; }';
                        content += '.wp-pointer-content .button-secondary{  left: -25px;background: transparent;top: 5px; border: 0;position: relative; padding: 0; box-shadow: none;margin: 0;color: #0085ba;} .wp-pointer-content .button-primary{ display:none}  #afw_mc_embed_signup{background:#fff; clear:left; font:14px Helvetica,Arial,sans-serif; }';
                        content += '</style>';                        
                        content += '<div id="afw_mc_embed_signup">';
                        content += '<form action="//app.mailerlite.com/webforms/submit/v5p5a5" data-id="258182" data-code="v5p5a5" method="POST" target="_blank">';
                        content += '<div id="afw_mc_embed_signup_scroll">';
                        content += '<div class="afw-mc-field-group" style="    margin-left: 15px;    width: 195px;    float: left;">';
                        content += '<input type="text" name="fields[name]" class="form-control" placeholder="Name" hidden value="'+pwaforwp_obj.current_user_name+'" style="display:none">';
                        content += '<input type="text" value="'+pwaforwp_obj.current_user_email+'" name="fields[email]" class="form-control" placeholder="Email*"  style="      width: 180px;    padding: 6px 5px;">';
                        content += '<input type="text" name="fields[company]" class="form-control" placeholder="Website" hidden style=" display:none; width: 168px; padding: 6px 5px;" value="'+pwaforwp_obj.get_home_url+'">';
                        content += '<input type="hidden" name="ml-submit" value="1" />';
                        content += '</div>';
                        content += '<div id="mce-responses">';
                        content += '<div class="response" id="mce-error-response" style="display:none"></div>';
                        content += '<div class="response" id="mce-success-response" style="display:none"></div>';
                        content += '</div>';
                        content += '<div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_a631df13442f19caede5a5baf_c9a71edce6" tabindex="-1" value=""></div>';
                        content += '<input type="submit" value="Subscribe" name="subscribe" id="pointer-close" class="button mc-newsletter-sent" style=" background: #0085ba; border-color: #006799; padding: 0px 16px; text-shadow: 0 -1px 1px #006799,1px 0 1px #006799,0 1px 1px #006799,-1px 0 1px #006799; height: 30px; margin-top: 1px; color: #fff; box-shadow: 0 1px 0 #006799;">';
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
    
    jQuery(".pwaforwp-colorpicker").wpColorPicker(); // Color picker
    jQuery(".pwaforwp-fcm-push-icon-upload").click(function(e) { // Application Icon upload
        e.preventDefault();
        var pwaforwpMediaUploader = wp.media({
            title: pwaforwp_obj.uploader_title,
            button: {
                text: pwaforwp_obj.uploader_button
            },
            multiple: false,  // Set this to true to allow multiple files to be selected
                        library:{type : 'image'}
        })
        .on("select", function() {
            var attachment = pwaforwpMediaUploader.state().get("selection").first().toJSON();
            jQuery(".pwaforwp-fcm-push-icon").val(attachment.url);
        })
        .open();
    });
    jQuery(".pwaforwp-icon-upload").click(function(e) {  // Application Icon upload
        e.preventDefault();
        var pwaforwpMediaUploader = wp.media({
            title: pwaforwp_obj.uploader_title,
            button: {
                text: pwaforwp_obj.uploader_button
            },
            multiple: false,  // Set this to true to allow multiple files to be selected
                        library:{type : 'image'}
        })
        .on("select", function() {
            var attachment = pwaforwpMediaUploader.state().get("selection").first().toJSON();
            jQuery(".pwaforwp-icon").val(attachment.url);
        })
        .open();
    });
    jQuery(".pwaforwp-splash-icon-upload").click(function(e) {   // Splash Screen Icon upload
        e.preventDefault();
        var pwaforwpMediaUploader = wp.media({
            title: pwaforwp_obj.splash_uploader_title,
            button: {
                text: pwaforwp_obj.uploader_button
            },
            multiple: false,  // Set this to true to allow multiple files to be selected
                        library:{type : 'image'}
        })
        .on("select", function() {
            var attachment = pwaforwpMediaUploader.state().get("selection").first().toJSON();
            jQuery(".pwaforwp-splash-icon").val(attachment.url);
        })
        .open();
    });

    jQuery(".pwaforwp-tabs a").click(function(e){
        var href = jQuery(this).attr("href");
        var currentTab = pwaforwpGetParamByName("tab",href);
        if(!currentTab){
            currentTab = "dashboard";
        }
        jQuery(this).siblings().removeClass("nav-tab-active");
        jQuery(this).addClass("nav-tab-active");
        jQuery(".form-wrap").find(".pwaforwp-"+currentTab).siblings().hide();
        jQuery(".form-wrap .pwaforwp-"+currentTab).show();       
        window.history.pushState("", "", href);
        if(currentTab=='help' || currentTab=='features'){
            jQuery('.pwaforwp-help').find("tr th:first").hide()
            jQuery('.pwaforwp-settings-form').find('p.submit').hide();
        }else{
             jQuery('.pwaforwp-settings-form').find('p.submit').show();
        }
        return false;
    });
    var url      = window.location.href;     // Returns full URL
    var currentTab = pwaforwpGetParamByName("tab",url);
    if(currentTab=='help' || currentTab=='features'){
        jQuery('.pwaforwp-help').find("tr th:first").hide()
        jQuery('.pwaforwp-settings-form').find('p.submit').hide();
    }
        
        jQuery(".pwaforwp-activate-service").on("click", function(e){
            jQuery(".pwaforwp-settings-form #submit").click();
            jQuery(this).hide();
        });
        jQuery(".pwaforwp-service-activate").on("click", function(){  
            
        var filetype = jQuery(this).attr("data-id");                
        
        if(filetype){
            
            jQuery.ajax({
                    url:ajaxurl,
                    dataType: "json",
                    data:{filetype:filetype, action:"pwaforwp_download_setup_files", pwaforwp_security_nonce:pwaforwp_obj.pwaforwp_security_nonce},
                    success:function(response){
                        if(response["status"]=="t"){
                            jQuery(".pwaforwp-service-activate[data-id="+filetype+"]").hide();
                            jQuery(".pwaforwp-service-activate[data-id="+filetype+"]").siblings(".dashicons").removeClass("dashicons-no-alt");
                            jQuery(".pwaforwp-service-activate[data-id="+filetype+"]").siblings(".dashicons").addClass("dashicons-yes");
                            jQuery(".pwaforwp-service-activate[data-id="+filetype+"]").siblings(".dashicons").css("color", "#46b450");
                        }else{
                            jQuery(".pwaforwp-service-activate[data-id="+filetype+"]").parent().next().removeClass("pwaforwp-hide");
                        }  
                    }                
                });
            
        }
                
        return false;
    });
        
    //Help Query
    jQuery(".pwa-send-query").on("click", function(e){
        e.preventDefault();   
        var message = jQuery("#pwaforwp_query_message").val();           
        var customer = jQuery("#pwaforwp_query_customer").val();    
        if($.trim(message) !='' && customer){       
                    jQuery.ajax({
                        type: "POST",    
                        url: ajaxurl,                    
                        dataType: "json",
                        data:{action:"pwaforwp_send_query_message", customer_type: customer, message:message, pwaforwp_security_nonce:pwaforwp_obj.pwaforwp_security_nonce},
                        success:function(response){                       
                          if(response['status'] =='t'){
                            jQuery(".pwa-query-success").show();
                            jQuery(".pwa-query-error").hide();
                          }else{
                            jQuery(".pwa-query-success").hide();  
                            jQuery(".pwa-query-error").show();
                          }
                        },
                        error: function(response){                    
                        console.log(response);
                        }
                        });
        }else{
            if($.trim(message) =='' && customer ==''){
                alert('Please enter the message and select customer type');
            }else{
            
            if(customer ==''){
                alert('Select Customer type');
            }
            if($.trim(message) == ''){
                alert('Please enter the message');
            }
                
            }
            
        }                   
        
    });
        
        jQuery(document).on("click",".pwaforwp-reset-settings", function(e){
                e.preventDefault();
             
                var reset_confirm = confirm("Are you sure?");
             
                if(reset_confirm == true){
                    
                jQuery.ajax({
                            type: "POST",    
                            url:ajaxurl,                    
                            dataType: "json",
                            data:{action:"pwaforwp_reset_all_settings", pwaforwp_security_nonce:pwaforwp_obj.pwaforwp_security_nonce},
                            success:function(response){                               
                                setTimeout(function(){ location.reload(); }, 1000);
                            },
                            error: function(response){                    
                            console.log(response);
                            }
                            }); 
                
                }
                                                                 

        });
        
        
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
                    url:pwaforwp_obj.ajax_url,                    
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
        
        jQuery(".pwaforwp-manual-notification").on("click", function(e){
        e.preventDefault();   
        var message = jQuery("#pwaforwp_notification_message").val(); 
        var pn_title   = jQuery("#pwaforwp_notification_message_title").val(); 
        var pn_url   = jQuery("#pwaforwp_notification_message_url").val(); 
            
            if(jQuery.trim(message) !=''){
                
                jQuery.ajax({
                        type: "POST",    
                        url: ajaxurl,                    
                        dataType: "json",
                        data:{action:"pwaforwp_send_notification_manually", message:message, title:pn_title, pwaforwp_security_nonce:pwaforwp_obj.pwaforwp_security_nonce,url:pn_url},
                        success:function(response){                                 
                          if(response['status'] =='t'){
                                var html = '<span style="color:green">Success: '+response['success']+'</span><br>';
                                    //html +='<span style="color:red;">Failure: '+response['failure']+'</span>';
                            jQuery(".pwaforwp-notification-success").show();
                                jQuery(".pwaforwp-notification-success").html(html);
                            jQuery(".pwaforwp-notification-error").hide();
                          }else{
                            jQuery(".pwaforwp-notification-success").hide();  
                            jQuery(".pwaforwp-notification-error").show();
                          }
                        },
                        error: function(response){                    
                        console.log(response);
                        }
                        });
                
            }else{
                alert('Please enter the message');
            }
            
                    
        
    });
        
    jQuery("#pwaforwp_settings_utm_setting").click(function(){
        
        if(jQuery(this).prop("checked")){
            jQuery('.pwawp_utm_values_class').fadeIn();
        }else{
            jQuery('.pwawp_utm_values_class').fadeOut(200);
        }
    });
        
        
        jQuery("#pwaforwp_settings_precaching_automatic").change(function(){ 
        if(jQuery(this).prop("checked")){
            jQuery("#pwaforwp_settings_precaching_post_count").parent().parent().fadeIn(); 
                        jQuery(".pwaforwp-pre-cache-table").parent().parent().fadeIn(); 
        }else{
            jQuery("#pwaforwp_settings_precaching_post_count").parent().parent().fadeOut(200);
                        jQuery(".pwaforwp-pre-cache-table").parent().parent().fadeOut(200);
        }
    }).change();
        
        jQuery("#pwaforwp_settings_precaching_manual").change(function(){    
        if(jQuery(this).prop("checked")){
            jQuery("#pwaforwp_settings_precaching_urls").parent().parent().parent().fadeIn();                                                
        }else{
            jQuery("#pwaforwp_settings_precaching_urls").parent().parent().parent().fadeOut(200);;
        }
    }).change();
        
        jQuery(document).on("click", ".pwaforwp-update-pre-caching-urls", function(e){
            e.preventDefault();
            var current = jQuery(this);
             jQuery.ajax({
                    url:ajaxurl,
                    dataType: "json",
                    data:{action:"pwaforwp_update_pre_caching_urls", pwaforwp_security_nonce:pwaforwp_obj.pwaforwp_security_nonce},
                    success:function(response){
                        if(response["status"]=="t"){
                                current.parent().parent().hide();
                        }else{
                                alert('Something went wrong');
                        }   
                    }                
                });
            
        })
        
        jQuery("#pwaforwp_precaching_method_selector").change(function(){
    
        if(jQuery(this).val() === 'automatic'){
            jQuery('.pwaforwp_precaching_table tr').eq(1).fadeIn();
                        jQuery('.pwaforwp_precaching_table tr').eq(2).fadeOut(200);
        }else{
                        jQuery('.pwaforwp_precaching_table tr').eq(1).fadeOut(200);
                        jQuery('.pwaforwp_precaching_table tr').eq(2).fadeIn();
            
        }
    });
        
        jQuery(".pwaforwp-add-to-home-banner-settings").click(function(){
        
        if(jQuery(this).prop("checked")){
            jQuery('.pwaforwp-enable-on-desktop').removeClass('afw_hide');
        }else{
            jQuery('.pwaforwp-enable-on-desktop').addClass('afw_hide');
                        jQuery('#enable_add_to_home_desktop_setting').prop('checked', false); // Checks it

        }
    });
        jQuery(".pwaforwp-onesignal-support").click(function(){      
        if(jQuery(this).prop("checked")){
            jQuery('.pwaforwp-onesignal-instruction').fadeIn();
        }else{
            jQuery('.pwaforwp-onesignal-instruction').fadeOut(200);
        }
    });
    jQuery('.pwawp_utm_values_class').find('input').focusout(function(){
        if(jQuery(this).attr('data-val')!==jQuery(this).val()){
            jQuery("#pwa-utm_change_track").val('1');
        }
    });
        
        jQuery(".pwaforwp-fcm-checkbox").click(function(){
            
                if(jQuery(this).prop("checked")){
                    jQuery(this).parent().find('p').removeClass('pwaforwp-hide');
        }else{
                    jQuery(this).parent().find('p').addClass('pwaforwp-hide');
        }
            
        });
    jQuery('.pwaforwp-checkbox-tracker').change(function(e){
        var respectiveId = jQuery(this).attr('data-id');
        var chval = 0;
        if(jQuery(this).is(":checked")){
            chval = jQuery(this).val();
        }
        console.log(jQuery(this).parent('label').find('#'+respectiveId), chval);
        jQuery(this).parent('label').find('input[name="'+respectiveId+'"]').val(chval);
    })
        
        //Licensing jquery starts here
    jQuery(document).on("click",".pwaforwp_license_activation", function(e){
                e.preventDefault();
                var current = jQuery(this);
                current.addClass('updating-message');
                var license_status = jQuery(this).attr('license-status');
                var add_on         = jQuery(this).attr('add-on');
                var license_key    = jQuery("#"+add_on+"_addon_license_key").val();
               
            if(license_status && add_on && license_key){
                
                jQuery.ajax({
                            type: "POST",    
                            url:ajaxurl,                    
                            dataType: "json",
                            data:{action:"pwaforwp_license_status_check",license_key:license_key,license_status:license_status, add_on:add_on, pwaforwp_security_nonce:pwaforwp_obj.pwaforwp_security_nonce},
                            success:function(response){                               
                               
                               jQuery("#"+add_on+"_addon_license_key_status").val(response['status']);
                                                                
                              if(response['status'] =='active'){  
                               jQuery(".saswp-"+add_on+"-dashicons").addClass('dashicons-yes');
                               jQuery(".saswp-"+add_on+"-dashicons").removeClass('dashicons-no-alt');
                               jQuery(".saswp-"+add_on+"-dashicons").css("color", "green");
                               
                               jQuery(".pwaforwp_license_activation[add-on='" + add_on + "']").attr("license-status", "inactive");
                               jQuery(".pwaforwp_license_activation[add-on='" + add_on + "']").text("Deactivate");
                               
                               jQuery(".pwaforwp_license_status_msg[add-on='" + add_on + "']").text('Activated');
                               
                               jQuery(".pwaforwp_license_status_msg[add-on='" + add_on + "']").css("color", "green");                                
                               jQuery(".pwaforwp_license_status_msg[add-on='" + add_on + "']").text(response['message']);
                                                                                             
                              }else{
                                  
                               jQuery(".saswp-"+add_on+"-dashicons").addClass('dashicons-no-alt');
                               jQuery(".saswp-"+add_on+"-dashicons").removeClass('dashicons-yes');
                               jQuery(".saswp-"+add_on+"-dashicons").css("color", "red");
                               
                               jQuery(".pwaforwp_license_activation[add-on='" + add_on + "']").attr("license-status", "active");
                               jQuery(".pwaforwp_license_activation[add-on='" + add_on + "']").text("Activate");
                               
                               jQuery(".pwaforwp_license_status_msg[add-on='" + add_on + "']").css("color", "red"); 
                               jQuery(".pwaforwp_license_status_msg[add-on='" + add_on + "']").text(response['message']);
                              }
                               current.removeClass('updating-message');                                                           
                            },
                            error: function(response){                    
                                console.log(response);
                            }
                            });
                            
            }else{
                alert('Please enter value license key');
                current.removeClass('updating-message'); 
            }

        });
        //Licensing jquery ends here
        
        jQuery('.pwaforwp-sub-tab-headings span').click(function(){
            var tabId = jQuery(this).attr('data-tab-id');
            jQuery(this).parents('.pwaforwp-subheading-wrap').find('.pwaforwp-subheading').find('div.selected').removeClass('selected').addClass('pwaforwp-hide');
            jQuery(this).parents('.pwaforwp-subheading-wrap').find('.pwaforwp-subheading').find('#'+tabId).removeClass('pwaforwp-hide').addClass('selected');
            //tab head
            jQuery(this).parent('.pwaforwp-sub-tab-headings').find('span.selected').removeClass('selected');
            jQuery(this).addClass('selected');
        });

        jQuery(".pwaforwp-checkbox").click(function(){
            
                    var data_id = jQuery(this).attr('data-id');
                    console.log(data_id);
            if(jQuery(this).prop("checked")){
                jQuery('.pwaforwp_'+data_id).removeClass('pwaforwp-hide');
            }else{
                jQuery('.pwaforwp_'+data_id).addClass('pwaforwp-hide');
            }
        });

    //ios splash screen start
    jQuery(".switch_apple_splash_screen").click(function(){
        if(jQuery(this).is(':checked')){
            jQuery('.ios-splash-images').show();
        }else{
            jQuery('.ios-splash-images').hide();
        }
    });
    jQuery(".pwaforwp-ios-splash-icon-upload").click(function(e) {   // Splash Screen Icon upload
        e.preventDefault();
        var self = jQuery(this);
        var splash_uploader_title = self.parent('.ios-splash-images-field').find('label').text();
        var pwaforwpMediaUploader = wp.media({
            title: splash_uploader_title,
            button: {
                text: 'Select image'
            },
            multiple: false,  // Set this to true to allow multiple files to be selected
                        library:{type : 'image'}
        })
        .on("select", function() {
            var attachment = pwaforwpMediaUploader.state().get("selection").first().toJSON();
            self.parent('.ios-splash-images-field').find(".pwaforwp-splash-icon").val(attachment.url);
        })
        .open();
    });
    //ios splash screen End



    jQuery('.pwaforwp-change-data').click(function(e){
        e.preventDefault();
        if(!jQuery(this).parents('.card-action').find('label').find('input[type="checkbox"]').prop('checked')){
            return false;
        }
        var opt = jQuery(this).attr('data-option');
        var optTitle = jQuery(this).attr('title');
        tb_show(optTitle, "#TB_inline?width=750&height=450&inlineId="+opt);
        datafeatureSubmit(opt);
    });

    jQuery('.card-action input[type="checkbox"]').change(function(){
        var value = 0;
        if(jQuery(this).is(':checked')){
            jQuery(this).parents('.card-action').find('.card-action-settings').css({opacity: 1})
            var value = 1;
            //jQuery(this).parents('.card-action').find('.pwaforwp-change-data').click();
        }else{
            jQuery(this).parents('.card-action').find('.card-action-settings').css({opacity: 0});
        }
        fields = [];
        var name = jQuery(this).attr('name');
        pwaforwp_dependent_features_section(name, value);
        fields.push({var_name: name, var_value: value});
        pwaforwp_ajaxcall_submitdata(pwaforwp_obj, fields);
    })

    /**
    * Push notification options selection
    */
    jQuery(document).on("change", ".pwaforwp-pn-service", function(){
        var self = jQuery(this);
        var currentSelected = self.val();
        pushnotificationIntegrationLogic('notification-contents');
        switch(currentSelected){
            case 'pushnotifications_io':
                jQuery('.pwaforwp-push-notificatoin-table').hide();
                jQuery('.pwaforwp-notification-condition-section').hide();
                jQuery('.pwaforwp-pn-recommended-options').show();
                //self.parents('.pwaforwp-wrap').find('.footer').hide();
            break;
            case 'fcm_push':
                jQuery('.pwaforwp-push-notificatoin-table').show();
                jQuery('.pwaforwp-notification-condition-section').show();
                jQuery('.pwaforwp-pn-recommended-options').hide();
                //self.parents('.pwaforwp-wrap').find('.footer').show();
            break;
            default:
                jQuery('.pwaforwp-push-notificatoin-table').hide();
                jQuery('.pwaforwp-notification-condition-section').hide();
                jQuery('.pwaforwp-pn-recommended-options').hide();
            break
        }
        jQuery('.notification-wrap-tb').find('.footer button').trigger('click')
    });

    jQuery('.pwaforwp-install-require-plugin').on("click", function(e){
        e.preventDefault();
        /*var result = confirm("This required a free plugin to install in your WordPress");
        if (!result) {

        }*/
        var self = jQuery(this);
        self.html('Installing..').addClass('updating-message');
        var nonce = self.attr('data-secure');
        var activate_url = self.attr('data-activate-url');
        var currentId = self.attr('id');
        var activate = '';
         if (currentId == 'pushnotification') {
                activate = '&activate=pushnotification';
            }

        console.log(wp.updates);


        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: 'action=pwafowp_enable_modules_upgread' + activate + '&verify_nonce=' + nonce,
            dataType: 'json',
            success: function (response) {
                if (response.status == 200) {
                    if (self.hasClass('not-exist')) {

                        //To installation
                        wp.updates.installPlugin(
                            {
                                slug: response.slug,
                                success: function (pluginresponse) {
                                    console.log(pluginresponse.activateUrl);
                                    pwaforwp_Activate_Modules_Upgrade(pluginresponse.activateUrl, self, response, nonce)
                                }
                            }
                        );

                    } else {
                        var activateUrl = self.attr('data-activate-url');
                        pwaforwp_Activate_Modules_Upgrade(activateUrl, self, response, nonce)
                    }
                } else {
                    alert(response.message)
                }

            }
        });
    });

});
var pushnotificationIntegrationLogic = function(opt){
    if(opt==='notification-contents'){
            var optNotif = jQuery('.pwaforwp-pn-service').val()
            if(optNotif==='' || optNotif==='pushnotifications_io'){
                jQuery('.notification-wrap-tb').find('.footer').hide()
            }else{
                jQuery('.notification-wrap-tb').find('.footer').show()
            }
        }
}
var datafeatureSubmit = function(opt){
        pushnotificationIntegrationLogic(opt)
        jQuery('.pwaforwp-submit-feature-opt').click(function(){
            /*jQuery('#TB_closeWindowButton').click();
            setTimeout(1000, function(){
                jQuery('.pwaforwp-main-wrapper').find('form').find('#submit').click();

            })*/
            var self = jQuery(this);
            var fields = [];
            self.parents('.thickbox-fetures-wrap')
                .find('input').each( function(k,v){
                    var type = jQuery(this).attr('type').toLowerCase();
                    var name = jQuery(this).attr('name');//.replace(/pwaforwp_settings\[/,'').replace(/\]/, '');

                    if(type=='checkbox'){
                        if(jQuery(this).is(':checked')){
                            var value = jQuery(this).val();
                        }else{
                            var value = (jQuery(this).attr('data-uncheck-val')) ? jQuery(this).attr('data-uncheck-val') : 0;
                        }
                        if(name){
                            pwaforwp_dependent_features_section(name, value)
                            fields.push({var_name: name, var_value: value});
                        }
                    }
                    if(type=='radio'){
                        if(jQuery(this).is(':checked')){
                            var value = jQuery(this).val();
                        }else{
                            var value = (jQuery(this).attr('data-uncheck-val')) ? jQuery(this).attr('data-uncheck-val') : 0;
                        }
                        if(name){
                            fields.push({var_name: name, var_value: value});
                        }
                    }
                    if(type!='checkbox' && type!='radio' ){
                       var value = jQuery(this).val();
                        if(name){
                            fields.push({var_name: name, var_value: value});
                        }
                    }

                });
            self.parents('.thickbox-fetures-wrap')
                .find('textarea').each( function(k,v){
                    var name = jQuery(this).attr('name');
                    var value = jQuery(this).val();
                    if(name){
                        fields.push({var_name: name, var_value: value});
                    }
                })
            self.parents('.thickbox-fetures-wrap')
                .find('select').each( function(k,v){
                    var name = jQuery(this).attr('name');
                    var value = jQuery(this).val();
                    if(name){
                        fields.push({var_name: name, var_value: value});
                        /*only for push notificatio opt*/
                        if( name==='pwaforwp_settings[notification_options]' ){
                            if(value!==''){
                                jQuery('#notification-opt-stat').hide();
                            }else{
                                jQuery('#notification-opt-stat').show();
                            }
                        }
                    }
                })


            pwaforwp_ajaxcall_submitdata(pwaforwp_obj, fields)
                
        });
    }

    function pwaforwp_ajaxcall_submitdata(pwaforwp_security_nonce, fields){
        if(!staticAjaxCalled){
            var staticAjaxCalled = true;
        }
        if(staticAjaxCalled){
            var data = {action:"pwaforwp_update_features_options", pwaforwp_security_nonce:pwaforwp_obj.pwaforwp_security_nonce,fields_data: fields};
                jQuery.ajax({
                    url:ajaxurl,
                    method:'post',
                    dataType: "json",
                    data:data,
                    success:function(response){
                        staticAjaxCalled = false;
                        if(response["status"]==200){
                            pwaforwp_show_message_toast('success', response.message);
                        }else{
                            pwaforwp_show_message_toast('error', response.message);
                        }
                    }                
                });
        }
    }

    function pwaforwp_show_message_toast(type, message){
        var classes = "pwaforwp-toast-error"
        if(type=='success'){
            classes="pwaforwp-toast-success"
        }
        if(jQuery('.pwaforwp-toast-wrap').length){
            jQuery('.pwaforwp-toast-wrap').remove();
        }

        var messageDiv = '<div class="pwaforwp-toast-wrap bottom-left"><div class="pwaforwp-toast-single '+classes+'" style="text-align: left;"><span class="pwaforwp-toast-loader pwaforwp-toast-loaded" style="-webkit-transition: width 2.6s ease-in;                       -o-transition: width 2.6s ease-in;                       transition: width 2.6s ease-in;                       background-color: #9EC600;"></span>'+message+'<span class="close-pwaforwp-toast-single">×</span></div></div>';
        jQuery('body').append(messageDiv);

        setTimeout(function(){
            jQuery('.pwaforwp-toast-wrap').remove();
        }, 3000);
        jQuery('.close-pwaforwp-toast-single').click(function(){
            jQuery(this).parents('.pwaforwp-toast-wrap').remove();
        })
    }


var pwaforwp_dependent_features_section = function(fieldname, fieldValue){
    switch(fieldname){
        case 'pwaforwp_settings[precaching_feature]': 
            if(fieldValue==1){
                jQuery('input[name="pwaforwp_settings[precaching_automatic]"]').trigger('click');
                jQuery('input[name="pwaforwp_settings[precaching_automatic_post]"]').trigger('click');
            }else{
                jQuery('input[name="pwaforwp_settings[precaching_automatic]"]').trigger('click');
                jQuery('input[name="pwaforwp_settings[precaching_automatic_post]"]').trigger('click');
            }

        break;

        case 'pwaforwp_settings[precaching_automatic]': 
            if(fieldValue==1){
                jQuery('input[name="pwaforwp_settings[precaching_feature]"]').prop('checked', true);
            }else{
                jQuery('input[name="pwaforwp_settings[precaching_feature]"]').prop('checked', false);
                jQuery('input[name="pwaforwp_settings[precaching_feature]"]').parents('.card-action').find('.card-action-settings').css({opacity: 0});
            }

        break;

        case 'pwaforwp_settings[addtohomebanner_feature]': 
            if(fieldValue==1){
                jQuery('input[name="pwaforwp_settings[custom_add_to_home_setting]"]').trigger('click');
            }else{
                jQuery('input[name="pwaforwp_settings[custom_add_to_home_setting]"]').trigger('click');
            }

        break;

        case 'pwaforwp_settings[custom_add_to_home_setting]': 
            if(fieldValue==1){
                jQuery('input[name="pwaforwp_settings[addtohomebanner_feature]"]').prop('checked', true);
            }else{
                jQuery('input[name="pwaforwp_settings[addtohomebanner_feature]"]').prop('checked', false);
                jQuery('input[name="pwaforwp_settings[addtohomebanner_feature]"]').parents('.card-action').find('.card-action-settings').css({opacity: 0});
            }

        break;

        case 'pwaforwp_settings[loader_feature]': 
            if(fieldValue==1){
                jQuery('input[name="pwaforwp_settings[loading_icon]"]').trigger('click');
            }else{
                jQuery('input[name="pwaforwp_settings[loading_icon]"]').trigger('click');
            }

        break;

        case 'pwaforwp_settings[loading_icon]': 
            if(fieldValue==1){
                jQuery('input[name="pwaforwp_settings[loader_feature]"]').prop('checked', true);
            }else{
                jQuery('input[name="pwaforwp_settings[loader_feature]"]').prop('checked', false);
                jQuery('input[name="pwaforwp_settings[loader_feature]"]').parents('.card-action').find('.card-action-settings').css({opacity: 0});
            }

        break;

        case 'pwaforwp_settings[utmtracking_feature]': 
            if(fieldValue==1){
                jQuery('input[name="pwaforwp_settings[utm_setting]"]').trigger('click');
            }else{
                jQuery('input[name="pwaforwp_settings[utm_setting]"]').trigger('click');
            }

        break;
        case 'pwaforwp_settings[utm_setting]': 
            if(fieldValue==1){
                jQuery('input[name="pwaforwp_settings[utmtracking_feature]"]').prop('checked', true);
            }else{
                jQuery('input[name="pwaforwp_settings[utmtracking_feature]"]').prop('checked', false);
                jQuery('input[name="pwaforwp_settings[utmtracking_feature]"]').parents('.card-action').find('.card-action-settings').css({opacity: 0});
            }

        break;
    }
}



var pwaforwp_Activate_Modules_Upgrade = function(url, self, response, nonce){
        if (typeof url === 'undefined' || !url) {
            return;
        }
         console.log( 'Activating...' );
         self.html('Activating...');
         jQuery.ajax(
            {
                async: true,
                type: 'GET',
                //data: dataString,
                url: url,
                success: function () {
                    var msgplug = '';
                    if(self.attr('id')=='pushnotification'){
                        msgplug = 'push notification';
                        console.log("push notification Activated");
                        self.removeClass('updating-message')
                        self.removeClass("button")
                        self.removeClass('pwaforwp-install-require-plugin')
                        self.unbind('click');
                        self.html('<a target="_blank" href="'+response.redirect_url+'" style="color:#fff;text-decoration:none;">View Settings</a>');
                    }
                },
                error: function (jqXHR, exception) {
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status === 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status === 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                    console.log(msg);
                },
            }
        );
    }