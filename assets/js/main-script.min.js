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
                                
                wp_pointers_tour_opts = $.extend (wp_pointers_tour_opts, {
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
                                $.post (pwaforwp_obj.ajax_url, {
                                        pointer: 'pwaforwp_subscribe_pointer',
                                        action: 'dismiss-wp-pointer'
                                });
                        },
                        show: function(event, t){
                         t.pointer.css({'left':'170px', 'top':'160px'});
                      }                                               
                });
                setup = function () {
                        $(pwaforwp_obj.displayID).pointer(wp_pointers_tour_opts).pointer('open');
                         if (pwaforwp_obj.button2) {
                                jQuery ('#pointer-close').after ('<a id="pointer-primary" class="button-primary">' + pwaforwp_obj.button2+ '</a>');
                                jQuery ('#pointer-primary').click (function () {
                                        pwaforwp_obj.function_name;
                                });
                                jQuery ('#pointer-close').click (function () {
                                        $.post (pwaforwp_obj.ajax_url, {
                                                pointer: 'pwaforwp_subscribe_pointer',
                                                action: 'dismiss-wp-pointer'
                                        });
                                });
                         }
                };
                if (wp_pointers_tour_opts.position && wp_pointers_tour_opts.position.defer_loading) {
                        $(window).bind('load.wp-pointers', setup);
                }
                else {
                        setup ();
                }
                
            }
                
    /* Newletters js ends here */ 
    
    $(".pwaforwp-colorpicker").wpColorPicker(); // Color picker
    $(".pwaforwp-fcm-push-icon-upload").click(function(e) { // Application Icon upload
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
            $(".pwaforwp-fcm-push-icon").val(attachment.url);
        })
        .open();
    });
    $(".pwaforwp-icon-upload").click(function(e) {  // Application Icon upload
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
            $(".pwaforwp-icon").val(attachment.url);
        })
        .open();
    });
    $(".pwaforwp-splash-icon-upload").click(function(e) {   // Splash Screen Icon upload
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
            $(".pwaforwp-splash-icon").val(attachment.url);
        })
        .open();
    });

    $(".pwaforwp-tabs a").click(function(e){
        var href = $(this).attr("href");
        var currentTab = pwaforwpGetParamByName("tab",href);
        if(!currentTab){
            currentTab = "dashboard";
        }
        $(this).siblings().removeClass("nav-tab-active");
        $(this).addClass("nav-tab-active");
        $(".form-wrap").find(".pwaforwp-"+currentTab).siblings().hide();
        $(".form-wrap .pwaforwp-"+currentTab).show();       
        window.history.pushState("", "", href);
        if(currentTab=='help' || currentTab=='features'){
            $('.pwaforwp-help').find("tr th:first").hide()
            $('.pwaforwp-settings-form').find('p.submit').hide();
        }else{
             $('.pwaforwp-settings-form').find('p.submit').show();
        }
        return false;
    });
    var url      = window.location.href;     // Returns full URL
    var currentTab = pwaforwpGetParamByName("tab",url);
    if(currentTab=='help' || currentTab=='features'){
        $('.pwaforwp-help').find("tr th:first").hide()
        $('.pwaforwp-settings-form').find('p.submit').hide();
    }
        
        $(".pwaforwp-activate-service").on("click", function(e){
            $(".pwaforwp-settings-form #submit").click();
            $(this).hide();
        });
        $(".pwaforwp-service-activate").on("click", function(){  
            
        var filetype = $(this).attr("data-id");                
        
        if(filetype){
            
            $.ajax({
                    url:ajaxurl,
                    dataType: "json",
                    data:{filetype:filetype, action:"pwaforwp_download_setup_files", pwaforwp_security_nonce:pwaforwp_obj.pwaforwp_security_nonce},
                    success:function(response){
                        if(response["status"]=="t"){
                            $(".pwaforwp-service-activate[data-id="+filetype+"]").hide();
                            $(".pwaforwp-service-activate[data-id="+filetype+"]").siblings(".dashicons").removeClass("dashicons-no-alt");
                            $(".pwaforwp-service-activate[data-id="+filetype+"]").siblings(".dashicons").addClass("dashicons-yes");
                            $(".pwaforwp-service-activate[data-id="+filetype+"]").siblings(".dashicons").css("color", "#46b450");
                        }else{
                            $(".pwaforwp-service-activate[data-id="+filetype+"]").parent().next().removeClass("pwaforwp-hide");
                        }  
                    }                
                });
            
        }
                
        return false;
    });
        
    //Help Query
    $(".pwa-send-query").on("click", function(e){
        e.preventDefault();   
        var message = $("#pwaforwp_query_message").val();           
                    $.ajax({
                        type: "POST",    
                        url: ajaxurl,                    
                        dataType: "json",
                        data:{action:"pwaforwp_send_query_message", message:message, pwaforwp_security_nonce:pwaforwp_obj.pwaforwp_security_nonce},
                        success:function(response){                       
                          if(response['status'] =='t'){
                            $(".pwa-query-success").show();
                            $(".pwa-query-error").hide();
                          }else{
                            $(".pwa-query-success").hide();  
                            $(".pwa-query-error").show();
                          }
                        },
                        error: function(response){                    
                        console.log(response);
                        }
                        });
        
    });
        
        $(document).on("click",".pwaforwp-reset-settings", function(e){
                e.preventDefault();
             
                var reset_confirm = confirm("Are you sure?");
             
                if(reset_confirm == true){
                    
                $.ajax({
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
        
        
         $(".pwaforwp-feedback-notice-close").on("click", function(e){
          e.preventDefault();               
                $.ajax({
                    type: "POST",    
                    url:ajaxurl,                    
                    dataType: "json",
                    data:{action:"pwaforwp_review_notice_close", pwaforwp_security_nonce:pwaforwp_obj.pwaforwp_security_nonce},
                    success:function(response){                       
                      if(response['status'] =='t'){
                       $(".pwaforwp-feedback-notice").hide();
                      }
                    },
                    error: function(response){                    
                    console.log(response);
                    }
                    });
    
        });
        
        $(".pwaforwp-feedback-notice-remindme").on("click", function(e){
                  e.preventDefault();               
                $.ajax({
                    type: "POST",    
                    url:pwaforwp_obj.ajax_url,                    
                    dataType: "json",
                    data:{action:"pwaforwp_review_notice_remindme", pwaforwp_security_nonce:pwaforwp_obj.pwaforwp_security_nonce},
                    success:function(response){                       
                      if(response['status'] =='t'){
                       $(".pwaforwp-feedback-notice").hide();
                      }
                    },
                    error: function(response){                    
                    console.log(response);
                    }
                    });    
        });
        
        $(".pwaforwp-manual-notification").on("click", function(e){
        e.preventDefault();   
        var message = $("#pwaforwp_notification_message").val(); 
        var pn_title   = $("#pwaforwp_notification_message_title").val(); 
        var pn_url   = $("#pwaforwp_notification_message_url").val(); 
            
            if($.trim(message) !=''){
                
                $.ajax({
                        type: "POST",    
                        url: ajaxurl,                    
                        dataType: "json",
                        data:{action:"pwaforwp_send_notification_manually", message:message, title:pn_title, pwaforwp_security_nonce:pwaforwp_obj.pwaforwp_security_nonce,url:pn_url},
                        success:function(response){                                 
                          if(response['status'] =='t'){
                                var html = '<span style="color:green">Success: '+response['success']+'</span><br>';
                                    //html +='<span style="color:red;">Failure: '+response['failure']+'</span>';
                            $(".pwaforwp-notification-success").show();
                                $(".pwaforwp-notification-success").html(html);
                            $(".pwaforwp-notification-error").hide();
                          }else{
                            $(".pwaforwp-notification-success").hide();  
                            $(".pwaforwp-notification-error").show();
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
        
    $("#pwaforwp_settings_utm_setting").click(function(){
        
        if($(this).prop("checked")){
            $('.pwawp_utm_values_class').fadeIn();
        }else{
            $('.pwawp_utm_values_class').fadeOut(200);
        }
    });
        
        
        $("#pwaforwp_settings_precaching_automatic").change(function(){ 
        if($(this).prop("checked")){
            $("#pwaforwp_settings_precaching_post_count").parent().parent().fadeIn(); 
                        $(".pwaforwp-pre-cache-table").parent().parent().fadeIn(); 
        }else{
            $("#pwaforwp_settings_precaching_post_count").parent().parent().fadeOut(200);
                        $(".pwaforwp-pre-cache-table").parent().parent().fadeOut(200);
        }
    }).change();
        
        $("#pwaforwp_settings_precaching_manual").change(function(){    
        if($(this).prop("checked")){
            $("#pwaforwp_settings_precaching_urls").parent().parent().parent().fadeIn();                                                
        }else{
            $("#pwaforwp_settings_precaching_urls").parent().parent().parent().fadeOut(200);;
        }
    }).change();
        
        $(document).on("click", ".pwaforwp-update-pre-caching-urls", function(e){
            e.preventDefault();
            var current = $(this);
             $.ajax({
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
        
        $("#pwaforwp_precaching_method_selector").change(function(){
    
        if($(this).val() === 'automatic'){
            $('.pwaforwp_precaching_table tr').eq(1).fadeIn();
                        $('.pwaforwp_precaching_table tr').eq(2).fadeOut(200);
        }else{
                        $('.pwaforwp_precaching_table tr').eq(1).fadeOut(200);
                        $('.pwaforwp_precaching_table tr').eq(2).fadeIn();
            
        }
    });
        
        $(".pwaforwp-add-to-home-banner-settings").click(function(){
        
        if($(this).prop("checked")){
            $('.pwaforwp-enable-on-desktop').removeClass('afw_hide');
        }else{
            $('.pwaforwp-enable-on-desktop').addClass('afw_hide');
                        $('#enable_add_to_home_desktop_setting').prop('checked', false); // Checks it

        }
    });
        $(".pwaforwp-onesignal-support").click(function(){      
        if($(this).prop("checked")){
            $('.pwaforwp-onesignal-instruction').fadeIn();
        }else{
            $('.pwaforwp-onesignal-instruction').fadeOut(200);
        }
    });
    $('.pwawp_utm_values_class').find('input').focusout(function(){
        if($(this).attr('data-val')!==$(this).val()){
            $("#pwa-utm_change_track").val('1');
        }
    });
        
        $(".pwaforwp-fcm-checkbox").click(function(){
            
                if($(this).prop("checked")){
                    $(this).parent().find('p').removeClass('pwaforwp-hide');
        }else{
                    $(this).parent().find('p').addClass('pwaforwp-hide');
        }
            
        });
        
        //Licensing jquery starts here
    $(document).on("click",".pwaforwp_license_activation", function(e){
                e.preventDefault();
                var current = $(this);
                current.addClass('updating-message');
                var license_status = $(this).attr('license-status');
                var add_on         = $(this).attr('add-on');
                var license_key    = $("#"+add_on+"_addon_license_key").val();
               
            if(license_status && add_on && license_key){
                
                $.ajax({
                            type: "POST",    
                            url:ajaxurl,                    
                            dataType: "json",
                            data:{action:"pwaforwp_license_status_check",license_key:license_key,license_status:license_status, add_on:add_on, pwaforwp_security_nonce:pwaforwp_obj.pwaforwp_security_nonce},
                            success:function(response){                               
                               
                               $("#"+add_on+"_addon_license_key_status").val(response['status']);
                                                                
                              if(response['status'] =='active'){  
                               $(".saswp-"+add_on+"-dashicons").addClass('dashicons-yes');
                               $(".saswp-"+add_on+"-dashicons").removeClass('dashicons-no-alt');
                               $(".saswp-"+add_on+"-dashicons").css("color", "green");
                               
                               $(".pwaforwp_license_activation[add-on='" + add_on + "']").attr("license-status", "inactive");
                               $(".pwaforwp_license_activation[add-on='" + add_on + "']").text("Deactivate");
                               
                               $(".pwaforwp_license_status_msg[add-on='" + add_on + "']").text('Activated');
                               
                               $(".pwaforwp_license_status_msg[add-on='" + add_on + "']").css("color", "green");                                
                               $(".pwaforwp_license_status_msg[add-on='" + add_on + "']").text(response['message']);
                                                                                             
                              }else{
                                  
                               $(".saswp-"+add_on+"-dashicons").addClass('dashicons-no-alt');
                               $(".saswp-"+add_on+"-dashicons").removeClass('dashicons-yes');
                               $(".saswp-"+add_on+"-dashicons").css("color", "red");
                               
                               $(".pwaforwp_license_activation[add-on='" + add_on + "']").attr("license-status", "active");
                               $(".pwaforwp_license_activation[add-on='" + add_on + "']").text("Activate");
                               
                               $(".pwaforwp_license_status_msg[add-on='" + add_on + "']").css("color", "red"); 
                               $(".pwaforwp_license_status_msg[add-on='" + add_on + "']").text(response['message']);
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
        
        $('.pwaforwp-sub-tab-headings span').click(function(){
            var tabId = $(this).attr('data-tab-id');
            $(this).parents('.pwaforwp-subheading-wrap').find('.pwaforwp-subheading').find('div.selected').removeClass('selected').addClass('pwaforwp-hide');
            $(this).parents('.pwaforwp-subheading-wrap').find('.pwaforwp-subheading').find('#'+tabId).removeClass('pwaforwp-hide').addClass('selected');
            //tab head
            $(this).parent('.pwaforwp-sub-tab-headings').find('span.selected').removeClass('selected');
            $(this).addClass('selected');
        });

        $(".pwaforwp-checkbox").click(function(){
            
                    var data_id = $(this).attr('data-id');
                    console.log(data_id);
            if($(this).prop("checked")){
                $('.pwaforwp_'+data_id).removeClass('pwaforwp-hide');
            }else{
                $('.pwaforwp_'+data_id).addClass('pwaforwp-hide');
            }
        });

    //ios splash screen start
    $(".switch_apple_splash_screen").click(function(){
        if($(this).is(':checked')){
            $('.ios-splash-images').show();
        }else{
            $('.ios-splash-images').hide();
        }
    });
    $(".pwaforwp-ios-splash-icon-upload").click(function(e) {   // Splash Screen Icon upload
        e.preventDefault();
        var self = $(this);
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



    $('.pwaforwp-change-data').click(function(e){
        e.preventDefault();
        if(!$(this).parents('.card-action').find('label').find('input[type="checkbox"]').prop('checked')){
            return false;
        }
        var opt = $(this).attr('data-option');
        var optTitle = $(this).attr('title');
        tb_show(optTitle, "#TB_inline?width=800&height=400&inlineId="+opt);
        datafeatureSubmit();
    });

    $('.card-action input[type="checkbox"]').change(function(){
        var value = 0;
        if($(this).is(':checked')){
            $(this).parents('.card-action').find('.card-action-settings').css({opacity: 1})
            var value = 1;
            //$(this).parents('.card-action').find('.pwaforwp-change-data').click();
        }else{
            $(this).parents('.card-action').find('.card-action-settings').css({opacity: 0});
        }
        fields = [];
        var name = $(this).attr('name');
        pwaforwp_dependent_features_section(name, value);
        fields.push({var_name: name, var_value: value});
        pwaforwp_ajaxcall_submitdata(pwaforwp_obj, fields);
    })

});

var datafeatureSubmit = function(){
        $('.pwaforwp-submit-feature-opt').click(function(){
            /*$('#TB_closeWindowButton').click();
            setTimeout(1000, function(){
                $('.pwaforwp-main-wrapper').find('form').find('#submit').click();

            })*/
            var self = $(this);
            var fields = [];
            self.parents('.thickbox-fetures-wrap')
                .find('input').each( function(k,v){
                    var type = $(this).attr('type').toLowerCase();
                    var name = $(this).attr('name');//.replace(/pwaforwp_settings\[/,'').replace(/\]/, '');

                    if(type=='checkbox'){
                        if($(this).is(':checked')){
                            var value = $(this).val();
                        }else{
                            var value = ($(this).attr('data-uncheck-val')) ? $(this).attr('data-uncheck-val') : 0;
                        }
                        if(name){
                            pwaforwp_dependent_features_section(name, value)
                            fields.push({var_name: name, var_value: value});
                        }
                    }
                    if(type=='radio'){
                        if($(this).is(':checked')){
                            var value = $(this).val();
                        }else{
                            var value = ($(this).attr('data-uncheck-val')) ? $(this).attr('data-uncheck-val') : 0;
                        }
                        if(name){
                            fields.push({var_name: name, var_value: value});
                        }
                    }
                    if(type!='checkbox' && type!='radio' ){
                       var value = $(this).val();
                        if(name){
                            fields.push({var_name: name, var_value: value});
                        }
                    }

                });
            self.parents('.thickbox-fetures-wrap')
                .find('textarea').each( function(k,v){
                    var name = $(this).attr('name');
                    var value = $(this).val();
                    if(name){
                        fields.push({var_name: name, var_value: value});
                    }
                })
            self.parents('.thickbox-fetures-wrap')
                .find('select').each( function(k,v){
                    var name = $(this).attr('name');
                    var value = $(this).val();
                    if(name){
                        fields.push({var_name: name, var_value: value});
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
                $.ajax({
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
        if($('.pwaforwp-toast-wrap').length){
            $('.pwaforwp-toast-wrap').remove();
        }

        var messageDiv = '<div class="pwaforwp-toast-wrap bottom-left"><div class="pwaforwp-toast-single '+classes+'" style="text-align: left;"><span class="pwaforwp-toast-loader pwaforwp-toast-loaded" style="-webkit-transition: width 2.6s ease-in;                       -o-transition: width 2.6s ease-in;                       transition: width 2.6s ease-in;                       background-color: #9EC600;"></span>'+message+'<span class="close-pwaforwp-toast-single">×</span></div></div>';
        $('body').append(messageDiv);

        setTimeout(function(){
            $('.pwaforwp-toast-wrap').remove();
        }, 3000);
        $('.close-pwaforwp-toast-single').click(function(){
            $(this).parents('.pwaforwp-toast-wrap').remove();
        })
    }


var pwaforwp_dependent_features_section = function(fieldname, fieldValue){
    switch(fieldname){
        case 'pwaforwp_settings[precaching_feature]': 
            if(fieldValue==1){
                $('input[name="pwaforwp_settings[precaching_automatic]"]').trigger('click');
                $('input[name="pwaforwp_settings[precaching_automatic_post]"]').trigger('click');
            }else{
                $('input[name="pwaforwp_settings[precaching_automatic]"]').trigger('click');
                $('input[name="pwaforwp_settings[precaching_automatic_post]"]').trigger('click');
            }

        break;

        case 'pwaforwp_settings[precaching_automatic]': 
            if(fieldValue==1){
                $('input[name="pwaforwp_settings[precaching_feature]"]').prop('checked', true);
            }else{
                $('input[name="pwaforwp_settings[precaching_feature]"]').prop('checked', false);
                $('input[name="pwaforwp_settings[precaching_feature]"]').parents('.card-action').find('.card-action-settings').css({opacity: 0});
            }

        break;

        case 'pwaforwp_settings[addtohomebanner_feature]': 
            if(fieldValue==1){
                $('input[name="pwaforwp_settings[custom_add_to_home_setting]"]').trigger('click');
            }else{
                $('input[name="pwaforwp_settings[custom_add_to_home_setting]"]').trigger('click');
            }

        break;

        case 'pwaforwp_settings[custom_add_to_home_setting]': 
            if(fieldValue==1){
                $('input[name="pwaforwp_settings[addtohomebanner_feature]"]').prop('checked', true);
            }else{
                $('input[name="pwaforwp_settings[addtohomebanner_feature]"]').prop('checked', false);
                $('input[name="pwaforwp_settings[addtohomebanner_feature]"]').parents('.card-action').find('.card-action-settings').css({opacity: 0});
            }

        break;

        case 'pwaforwp_settings[loader_feature]': 
            if(fieldValue==1){
                $('input[name="pwaforwp_settings[loading_icon]"]').trigger('click');
            }else{
                $('input[name="pwaforwp_settings[loading_icon]"]').trigger('click');
            }

        break;

        case 'pwaforwp_settings[loading_icon]': 
            if(fieldValue==1){
                $('input[name="pwaforwp_settings[loader_feature]"]').prop('checked', true);
            }else{
                $('input[name="pwaforwp_settings[loader_feature]"]').prop('checked', false);
                $('input[name="pwaforwp_settings[loader_feature]"]').parents('.card-action').find('.card-action-settings').css({opacity: 0});
            }

        break;

        case 'pwaforwp_settings[utmtracking_feature]': 
            if(fieldValue==1){
                $('input[name="pwaforwp_settings[utm_setting]"]').trigger('click');
            }else{
                $('input[name="pwaforwp_settings[utm_setting]"]').trigger('click');
            }

        break;
        case 'pwaforwp_settings[utm_setting]': 
            if(fieldValue==1){
                $('input[name="pwaforwp_settings[utmtracking_feature]"]').prop('checked', true);
            }else{
                $('input[name="pwaforwp_settings[utmtracking_feature]"]').prop('checked', false);
                $('input[name="pwaforwp_settings[utmtracking_feature]"]').parents('.card-action').find('.card-action-settings').css({opacity: 0});
            }

        break;
    }
}