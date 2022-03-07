var strict;

jQuery(document).ready(function ($) {
    /**
     * DEACTIVATION FEEDBACK FORM
     */
    // show overlay when clicked on "deactivate"
    pwa_deactivate_link = $('.wp-admin.plugins-php tr[data-slug="pwa-for-wp"] .row-actions .deactivate a');
    pwa_deactivate_link_url = pwa_deactivate_link.attr('href');

    pwa_deactivate_link.click(function (e) {
        e.preventDefault();

        // only show feedback form once per 30 days
        var c_value = pwa_admin_get_cookie("pwa_hide_deactivate_feedback");

        if (c_value === undefined) {
            $('#pwa-reloaded-feedback-overlay').show();
        } else {
            // click on the link
            window.location.href = pwa_deactivate_link_url;
        }
    });
    // show text fields
    $('#pwa-reloaded-feedback-content input[type="radio"]').click(function () {
        // show text field if there is one
        var inputValue = $(this).attr("value");
        var targetBox = $("." + inputValue);
        $(".mb-box").not(targetBox).hide();
        $(targetBox).show();
    });
    // send form or close it
    $('#pwa-reloaded-feedback-content .button').click(function (e) {
        e.preventDefault();
        // set cookie for 30 days
        var exdate = new Date();
        exdate.setSeconds(exdate.getSeconds() + 2592000);
        document.cookie = "pwa_hide_deactivate_feedback=1; expires=" + exdate.toUTCString() + "; path=/";

        $('#pwa-reloaded-feedback-overlay').hide();
        if ('pwa-reloaded-feedback-submit' === this.id) {
            // Send form data
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: 'pwa_send_feedback',
                    data: $('#pwa-reloaded-feedback-content form').serialize()
                },
                complete: function (MLHttpRequest, textStatus, errorThrown) {
                    // deactivate the plugin and close the popup
                    $('#pwa-reloaded-feedback-overlay').remove();
                    window.location.href = pwa_deactivate_link_url;

                }
            });
        } else {
            $('#pwa-reloaded-feedback-overlay').remove();
            window.location.href = pwa_deactivate_link_url;
        }
    });
    // close form without doing anything
    $('.pwa-for-wp-feedback-not-deactivate').click(function (e) {
        $('#pwa-reloaded-feedback-overlay').hide();
    });
    
    function pwa_admin_get_cookie (name) {
	var i, x, y, pwa_cookies = document.cookie.split( ";" );
	for (i = 0; i < pwa_cookies.length; i++)
	{
		x = pwa_cookies[i].substr( 0, pwa_cookies[i].indexOf( "=" ) );
		y = pwa_cookies[i].substr( pwa_cookies[i].indexOf( "=" ) + 1 );
		x = x.replace( /^\s+|\s+$/g, "" );
		if (x === name)
		{
			return unescape( y );
		}
	}
}

}); // document ready