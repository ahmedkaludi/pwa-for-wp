function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}


jQuery(document).ready(function($){
    $('.pwaforwp-colorpicker').wpColorPicker();	// Color picker
	$('.pwaforwp-icon-upload').click(function(e) {	// Application Icon upload
		e.preventDefault();
		var pwaforwpMediaUploader = wp.media({
			title: 'Application Icon',
			button: {
				text: 'Select Icon'
			},
			multiple: false  // Set this to true to allow multiple files to be selected
		})
		.on('select', function() {
			var attachment = pwaforwpMediaUploader.state().get('selection').first().toJSON();
			$('.pwaforwp-icon').val(attachment.url);
		})
		.open();
	});
	$('.pwaforwp-splash-icon-upload').click(function(e) {	// Splash Screen Icon upload
		e.preventDefault();
		var pwaforwpMediaUploader = wp.media({
			title: 'Splash Screen Icon',
			button: {
				text: 'Select Icon'
			},
			multiple: false  // Set this to true to allow multiple files to be selected
		})
		.on('select', function() {
			var attachment = pwaforwpMediaUploader.state().get('selection').first().toJSON();
			$('.pwaforwp-splash-icon').val(attachment.url);
		})
		.open();
	});

	$('.pwaforwp-tabs a').click(function(e){
		var href = $(this).attr('href');
		var currentTab = getParameterByName('tab',href);
		if(!currentTab){
			currentTab = 'dashboard';
		}
		$(this).siblings().removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		$('.form-wrap').find('.pwaforwp-'+currentTab).siblings().hide();
		$('.form-wrap .pwaforwp-'+currentTab).show();
		window.history.pushState("", "", href);
		return false;
	});
        
        $(".pwaforwp-activate-service").on('click', function(e){
            $(".pwaforwp-settings-form #submit").click();
            $(this).hide();
        });
        
});
