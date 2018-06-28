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
});