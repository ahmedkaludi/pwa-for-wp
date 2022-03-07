<?php 
$reasons = array(
    		1 => '<li><label><input type="radio" name="pwa_disable_reason" value="temporary"/>' . __('It is only temporary', 'pwa-for-wp') . '</label></li>',
		2 => '<li><label><input type="radio" name="pwa_disable_reason" value="stopped"/>' . __('I stopped using PWA on my site', 'pwa-for-wp') . '</label></li>',
		3 => '<li><label><input type="radio" name="pwa_disable_reason" value="missing"/>' . __('I miss a feature', 'pwa-for-wp') . '</label></li>
		<li><input class="mb-box missing" type="text" name="pwa_disable_text[]" value="" placeholder="Please describe the feature"/></li>',
		4 => '<li><label><input type="radio" name="pwa_disable_reason" value="technical"/>' . __('Technical Issue', 'pwa-for-wp') . '</label></li>
		<li><textarea class="mb-box technical" name="pwa_disable_text[]" placeholder="' . __('How Can we help? Please describe your problem', 'pwa-for-wp') . '"></textarea></li>',
		5 => '<li><label><input type="radio" name="pwa_disable_reason" value="another"/>' . __('I switched to another plugin', 'pwa-for-wp') .  '</label></li>
		<li><input class="mb-box another" type="text" name="pwa_disable_text[]" value="" placeholder="Name of the plugin"/></li>',
		6 => '<li><label><input type="radio" name="pwa_disable_reason" value="other"/>' . __('Other reason', 'pwa-for-wp') . '</label></li>
		<li><textarea class="mb-box other" name="pwa_disable_text[]" placeholder="' . __('Please specify, if possible', 'pwa-for-wp') . '"></textarea></li>',
    );
shuffle($reasons);
?>


<div id="pwa-reloaded-feedback-overlay" style="display: none;">
    <div id="pwa-reloaded-feedback-content">
	<form action="" method="post">
	    <h3><strong><?php _e('If you have a moment, please let us know why you are deactivating:', 'pwa-for-wp'); ?></strong></h3>
	    <ul>
                <?php 
                foreach ($reasons as $reason){
                    echo $reason;
                }
                ?>
	    </ul>
	    <?php if ($email) : ?>
    	    <input type="hidden" name="pwa_disable_from" value="<?php echo $email; ?>"/>
	    <?php endif; ?>
	    <input id="pwa-reloaded-feedback-submit" class="button button-primary" type="submit" name="pwa_disable_submit" value="<?php _e('Submit & Deactivate', 'pwa-for-wp'); ?>"/>
	    <a class="button"><?php _e('Only Deactivate', 'pwa-for-wp'); ?></a>
	    <a class="pwa-for-wp-feedback-not-deactivate" href="#"><?php _e('Don\'t deactivate', 'pwa-for-wp'); ?></a>
	</form>
    </div>
</div>