<?php
/**
 * Functions for app shortcuts features
 *
 * @since 1.6
 */
// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}
/**
 * APP shortcut feature class file
 */
class PWAForWP_APP_Shortcut
{
    /**
     * __construct function
     * initiate the class
     */
    public function __construct()
    {
        if (is_admin()) {
            // $this->adminFeature();
        } else {
            //   $this->frontend_feature();
        }
    }
    /**
     * All settings of this features
     *
     * @return void
     */
    public static function adminFeature()
    {
        add_settings_section('pwaforwp_quick_shortcut_setting_section', esc_html__(' ', 'pwa-for-wp'), '__return_false', 'pwaforwp_quick_shortcut_setting_section');
        // Splash Screen Background Color
        add_settings_field(
            'pwaforwp_push_notification',
            __return_false(),
            array('PWAforWP_APP_Shortcut', 'quickShortcutSettingsCallback'),
            'pwaforwp_quick_shortcut_setting_section',
            'pwaforwp_quick_shortcut_setting_section'
        );
    }
    
    /**
     * Admin features all work initiate form here
     *
     * @return void
     */
    public static function quickShortcutSettingsCallback()
    {
        $pwaforwp_settings = pwaforwp_defaultSettings();
        if ( isset($pwaforwp_settings['shortcut']) && !empty($pwaforwp_settings['shortcut']) ) 
        {
            $shortcutSettings = $pwaforwp_settings['shortcut'];
        } else {
            $shortcutSettings = $pwaforwp_settings['shortcut'];
        }
        ?>
            <div class="pwaforwp-wrp">
                <p class="desc-heading">You can create here multiple shortcuts, its will looks like 
                <a href="#">Tutorial</a></p>
                <div class="pwaforwp-repeat-wrap">
                    <div class="pwaforwp-repeat-sec">
                        <div class="field label">Name</div>
                        <div class="field label">Short name</div>
                        <div class="field label">Description</div>
                        <div class="field label">URL</div>
                        <div class="field label">Icons</div>
                        <div class="field action"></div>
                    </div>
                    <div class="pwaforwp-repeat-sec">
                        <div class="field">
                            <input type="text" name="pwaforwp_settings[shortcut][0][name]">
                        </div>
                        <div class="field">
                            <input type="text" name="pwaforwp_settings[shortcut][0][short_name]">
                        </div>
                        <div class="field">
                            <input type="text" name="pwaforwp_settings[shortcut][0][description]">
                        </div>
                        <div class="field">
                            <input type="text" name="pwaforwp_settings[shortcut][0][url]">
                        </div>
                        <div class="field">
                            <input type="text" name="pwaforwp_settings[shortcut][0][icons]" class="pwaforwp-icon">
                            <button type="button" class="button pwaforwp-set-shortcuticon" data-editor="content">
                                <span class="dashicons dashicons-format-image" style="margin-top: 4px;"></span> 
                            </button>
                        </div>
                        <div class="field action">
                            <span class="dashicons dashicons-trash pwaforwp-repeat-trash"></span> 
                            
                        </div>
                    </div>
                </div>
                <div class="pwaforwp-inc-desc">
                    <button type="button" id="addition-shortcut">+ Add new shortcut</button>
                </div>
            </div>
            <script type="template/javascript" id="pfw-shortcut-template"><div class="pwaforwp-repeat-sec"><div class="field"> <input type="text" name="pwaforwp_settings[shortcut][%i%][name]"></div><div class="field"> <input type="text" name="pwaforwp_settings[shortcut][%i%][short_name]"></div><div class="field"> <input type="text" name="pwaforwp_settings[shortcut][%i%][description]"></div><div class="field"> <input type="text" name="pwaforwp_settings[shortcut][%i%][url]"></div><div class="field"> <input type="text" name="pwaforwp_settings[shortcut][%i%][icons]" class="pwaforwp-icon"> <button type="button" class="button pwaforwp-set-shortcuticon" data-editor="content"> <span class="dashicons dashicons-format-image" style="margin-top: 4px;"></span> </button></div><div class="field action"> <span class="dashicons dashicons-trash pwaforwp-repeat-trash"></span></div></div></script>
        <?php
    }
}
$pwaforwp_appshortcut = new PWAforWP_APP_Shortcut();
