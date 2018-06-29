jQuery(document).ready(function($){
    $('.ampforwp-pwa-colorpicker').wpColorPicker();	// Color picker
	$('.ampforwp-pwa-icon-upload').click(function(e) {	// Application Icon upload
		e.preventDefault();
		var amppwa_meda_uploader = wp.media({
			title: 'Application Icon',
			button: {
				text: 'Select Icon'
			},
			multiple: false  // Set this to true to allow multiple files to be selected
		})
		.on('select', function() {
			var attachment = amppwa_meda_uploader.state().get('selection').first().toJSON();
			$('.amppwa-icon').val(attachment.url);
		})
		.open();
	});
	$('.amppwa-splash-icon-upload').click(function(e) {	// Splash Screen Icon upload
		e.preventDefault();
		var amppwa_meda_uploader = wp.media({
			title: 'Splash Screen Icon',
			button: {
				text: 'Select Icon'
			},
			multiple: false  // Set this to true to allow multiple files to be selected
		})
		.on('select', function() {
			var attachment = amppwa_meda_uploader.state().get('selection').first().toJSON();
			$('.amppwa-splash-icon').val(attachment.url);
		})
		.open();
	});

	$('.amppwa-tabs a').click(function(e){
		var href = $(this).attr('href');
		var currentTab = getParameterByName('tab',href);
		if(!currentTab){
			currentTab = 'dashboard';
		}
		$(this).siblings().removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		$('.form-wrap').find('.amp-pwa-'+currentTab).siblings().hide();
		$('.form-wrap .amp-pwa-'+currentTab).show();
		window.history.pushState("", "", href);

		return false;
	})
});
function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}