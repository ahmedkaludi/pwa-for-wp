<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

Class Pwaforwp_webpushr{
	/**
	 * get the unique instance of the class
	 * @var Pwaforwp_webpushr
	 */
	private static $instance;

	private $settings = array();
	/**
	 * Constructor
	 */
	function __construct(){
		if(!$this->settings){
			$this->settings = pwaforwp_defaultSettings();
		}
		add_action('plugins_loaded', array($this, 'web_init'));
	}
	/**
	 * Check and entry function for compatibility with webpushr plugin
	 * @return null
	 */
	public function web_init(){
		if(isset($this->settings['webpusher_support_setting']) && $this->settings['webpusher_support_setting']==1){
			// add_filter( 'pwaforwp_sw_name_modify', array($this, 'change_sw_name') );
			add_filter("pwaforwp_sw_js_template", array($this, 'add_webpushr'));
			
		}
	}
	/**
     * Gets an instance of our Pwaforwp_webpushr class.
     *
     * @return Pwaforwp_webpushr
     */
	public static function get_instance(){
		if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
	}
	/**
	 * add content in service worker for webpushr call
	 * @param string $content javascript content
	 * @return string $content javascript content
	 */
	public function add_webpushr($content){
		$content = "importScripts('https://cdn.webpushr.com/sw-server.min.js');\n".$content;
		return $content;
	}

}

function pwaforwp_webpushr(){
	return Pwaforwp_webpushr::get_instance();
}
pwaforwp_webpushr();