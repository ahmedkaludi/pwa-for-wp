<?php

class ServiceWorker{
	public $wppath;
	public function __construct(){
		$this->wppath = str_replace("//","/",str_replace("\\","/",realpath(ABSPATH))."/");
	}

	public function init(){
		//Admin Work
	
		//Service worker added
		add_action('amp_post_template_footer',array($this, 'ampforwp_service_worker'));
		//Load Script
		add_filter('amp_post_template_data',array($this, 'ampforwp_pwa_service_worker_script'));

		//Run time File Generate
		//sw.Js
		add_action( 'wp_ajax_ampforwp_pwa_wp_swjs', array($this, 'ampforwp_pwa_wp_swjs') );
		add_action( 'wp_ajax_nopriv_ampforwp_pwa_wp_swjs', array($this, 'ampforwp_pwa_wp_swjs') );//sw.Js

		//HTML FILES
		//sw.html
		add_action( 'wp_ajax_ampforwp_pwa_wp_swhtml', array($this, 'ampforwp_pwa_wp_swhtml') );
		add_action( 'wp_ajax_nopriv_ampforwp_pwa_wp_swhtml', array($this, 'ampforwp_pwa_wp_swhtml') );

		//404.html
		add_action( 'wp_ajax_ampforwp_pwa_wp_404html', array($this, 'ampforwp_pwa_wp_404html') );
		add_action( 'wp_ajax_nopriv_ampforwp_pwa_wp_404html', array($this, 'ampforwp_pwa_wp_404html') );

		//offline.html
		add_action( 'wp_ajax_ampforwp_pwa_wp_offlinehtml', array($this, 'ampforwp_pwa_wp_offlinehtml') );
		add_action( 'wp_ajax_nopriv_ampforwp_pwa_wp_offlinehtml', array($this, 'ampforwp_pwa_wp_offlinehtml') );
	}

	public function ampforwp_pwa_wp_404html(){
		header("Content-Type:text/html; charset=UTF-8");
		$swHtmlContent = file_get_contents(AMPFORWP_SERVICEWORKER_PLUGIN_DIR."layouts/404.html");
		echo $swHtmlContent;
		wp_die();
	}

	public function ampforwp_pwa_wp_swhtml(){
		header("Content-Type:text/html; charset=UTF-8");
		$swHtmlContent = file_get_contents(AMPFORWP_SERVICEWORKER_PLUGIN_DIR."layouts/sw.html");
		echo $swHtmlContent;
		wp_die();
	}

	public function ampforwp_pwa_wp_swjs(){
		header("Content-Type:text/javascript; charset=UTF-8");
		$swJsContent = file_get_contents(AMPFORWP_SERVICEWORKER_PLUGIN_DIR."sw.js");
		echo $swJsContent;
		wp_die();
	}

	public function ampforwp_pwa_wp_offlinehtml(){
		header("Content-Type:text/html; charset=UTF-8");
		$swHtmlContent = file_get_contents(AMPFORWP_SERVICEWORKER_PLUGIN_DIR."layouts/offline/index.html");
		echo $swHtmlContent;
		wp_die();
	}

	public static function ampforwp_remove_rewrite_rules_custom_rewrite(){
		if(strtolower( $this->ampforwp_sw_getwebserver() )=='apache'){
            $raw_rules = file_get_contents($this->wppath.".htaccess");
			if(strpos($raw_rules, '#BEGIN ampforwp pwa')!==False){
				$rules = preg_replace('/#BEGIN ampforwp pwa(.*?)#End PWA /si', "\r", $raw_rules);
				if(!file_put_contents($this->wppath.".htaccess",$rules,LOCK_EX)) return false;
			}
        }
	}

	public function ampforwp_rewrite_rules_custom_rewrite(){
		if(strtolower( $this->ampforwp_sw_getwebserver() )=='apache'){
            $this->ampforwp_insert_htaccess( $this->ampforwp_getrewriterule(false) );
        }
	}

	public function ampforwp_insert_htaccess($rule){
	   $raw_rules = file_get_contents($this->wppath.".htaccess");
	   if(strpos($raw_rules, '#BEGIN ampforwp pwa')!==False){
	   	
	   }else{
	   	 $rules = $rule."\n\n".trim($raw_rules);
	   	  if(!file_put_contents($this->wppath.".htaccess",$rules,LOCK_EX)) return false;
	   }
	    return true;
	}

	public function ampforwp_getrewriterule(){
		$home_root = parse_url(home_url());
		if ( isset( $home_root['path'] ) )
			$home_root = trailingslashit($home_root['path']);
		else
			$home_root = '/';

		$ajaxUrl = str_replace( site_url()."/", '', admin_url( 'admin-ajax.php' ) );
		
		$htaccessPwa = "#BEGIN ampforwp pwa\n#Must the First Rewrite Rule\n<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteBase ".$home_root."\nRewriteCond %{REQUEST_METHOD} !POST\n RewriteCond %{QUERY_STRING} !.*=.*\nRewriteRule ^sw.js$ ".$ajaxUrl."?action=ampforwp_pwa_wp_swjs [L]\nRewriteRule ^sw.html$ ".$ajaxUrl."?action=ampforwp_pwa_wp_swhtml [L]\nRewriteRule ^404.html$ ".$ajaxUrl."?action=ampforwp_pwa_wp_404html [L]\nRewriteRule ^offline_index.html$ ".$ajaxUrl."?action=ampforwp_pwa_wp_offlinehtml [L]\n</IfModule>\n<IfModule mod_headers.c>\n<FilesMatch \"\.(js)$\">\nHeader set Access-Control-Allow-Origin \"*\"\n</FilesMatch>\n</Ifmodule>\n#End PWA ";
		return $htaccessPwa;
	}
	

	public function ampforwp_sw_getwebserver(){
		    $software=strtolower($_SERVER["SERVER_SOFTWARE"]);
		    switch ($software){
		    case strstr($software,"nginx"):
		        return "nginx";
		        break;
		    case strstr($software,"apache"):
		        return "apache";
		        break;
		    case strstr($software,"iis"):
		        return "iis";
		        break;
		    default:
		        return "unknown";
		    }
		}

	public function ampforwp_service_worker(){
		$url = str_replace("http:","https:",site_url());
		?><amp-install-serviceworker src="<?php echo $url."/sw.js"; ?>" data-iframe-src="<?php echo $url.'/sw.html'; ?>"  layout="nodisplay">
			</amp-install-serviceworker>
		<?php
	}

		//Load Script
	public static function ampforwp_pwa_service_worker_script( $data ){
		if ( empty( $data['amp_component_scripts']['amp-install-serviceworker'] ) ) {
			$data['amp_component_scripts']['amp-install-serviceworker'] = 'https://cdn.ampproject.org/v0/amp-install-serviceworker-0.1.js';
		}
		return $data;
	}

}