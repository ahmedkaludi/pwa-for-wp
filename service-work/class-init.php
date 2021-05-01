<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Class PWAFORWP_File_Creation_Init
 */
class PWAFORWP_File_Creation_Init {
    
    public $wppath;
    public $fileCreation;
    public $swjs_init;
    public $minifest_init;
    public $swr_init;
    public $swjs_init_amp;
    public $minifest_init_amp;
    public $swhtml_init_amp;  
    public $firebase_manifest_init;
    public $push_notification_js;
             
    public function __construct(){
        
        $this->wppath                 = str_replace("//","/",str_replace("\\","/",realpath(ABSPATH))."/"); 
        $this->wppath                 = apply_filters("pwaforwp_file_creation_path", $this->wppath);
        $this->fileCreation           = new pwaforwpFileCreation();
        $this->swjs_init              = $this->wppath.apply_filters('pwaforwp_sw_name_modify',           "pwa-sw".pwaforwp_multisite_postfix().".js");
        $this->minifest_init          = $this->wppath.apply_filters('pwaforwp_manifest_file_name',     "pwa-manifest".pwaforwp_multisite_postfix().".json");
        $this->swr_init               = $this->wppath.apply_filters('pwaforwp_sw_file_name',           "pwa-register-sw".pwaforwp_multisite_postfix().".js");
        $this->swjs_init_amp          = $this->wppath.apply_filters('pwaforwp_amp_sw_file_name',       "pwa-amp-sw".pwaforwp_multisite_postfix().".js");
        $this->minifest_init_amp      = $this->wppath.apply_filters('pwaforwp_amp_manifest_file_name', "pwa-amp-manifest".pwaforwp_multisite_postfix().".json");
        $this->swhtml_init_amp        = $this->wppath.apply_filters('pwaforwp_amp_sw_html_file_name',  "pwa-amp-sw".pwaforwp_multisite_postfix().".html");
        $this->firebase_manifest_init = $this->wppath.apply_filters('pwaforwp_pn_manifest_file_name',  "pwa-push-notification-manifest".pwaforwp_multisite_postfix().".json");                         
        $this->push_notification_js   = PWAFORWP_PLUGIN_DIR.'/assets/js/pwa-push-notification'.pwaforwp_multisite_postfix().".js";                         
    }
    
    public function pwaforwp_push_notification_js($action = null){
        $pwaSettings = pwaforwp_defaultSettings();
        if( $pwaSettings['notification_feature']==1 && isset($pwaSettings['notification_options']) && $pwaSettings['notification_options']!='fcm_push'){
            return; 
        }
        $pnjs_strContent = $this->fileCreation->pwaforwp_pnjs();
        return pwaforwp_write_a_file($this->push_notification_js, $pnjs_strContent, $action);
                                               
    }
        
    public function pwaforwp_swjs_init($action = null){
        
        $swjsContent = $this->fileCreation->pwaforwp_swjs();
        return pwaforwp_write_a_file($this->swjs_init, $swjsContent, $action);
                                
    }
    
    public function pwaforwp_manifest_init($action = null){
        
        $swHtmlContent  = $this->fileCreation->pwaforwp_manifest();
        $swHtmlContent  = str_replace("&#038;", '&', $swHtmlContent);
        return pwaforwp_write_a_file($this->minifest_init, $swHtmlContent, $action);
                
    }
    
    public function pwaforwp_swr_init($action = null){   
        
        $swjsContent    = $this->fileCreation->pwaforwp_swr();
        return pwaforwp_write_a_file($this->swr_init, $swjsContent, $action);
                       
    }
    
    public function pwaforwp_swjs_init_amp($action = null){  
        
        $swjsContent    = $this->fileCreation->pwaforwp_swjs(true);
        return pwaforwp_write_a_file($this->swjs_init_amp, $swjsContent, $action);
        
     }
     public function pwaforwp_manifest_init_amp($action = null){
         
         $swHtmlContent = $this->fileCreation->pwaforwp_manifest(true);
         return pwaforwp_write_a_file($this->minifest_init_amp, $swHtmlContent, $action);
        
    }    
    public function pwaforwp_swhtml_init_amp($action = null){  
        
        $swHtmlContent = $this->fileCreation->pwaforwp_swhtml(true);
        return pwaforwp_write_a_file($this->swhtml_init_amp, $swHtmlContent, $action);
                 
    }
    public function pwaforwp_swhtml_init_firebase_js($action = null){  
        
        $settings 	= pwaforwp_defaultSettings(); 
        
        $server_key     = $settings['fcm_server_key'];
        $config         = $settings['fcm_config'];
                                
        $swjsContent    = $this->fileCreation->pwaforwp_swjs();
        $status         = pwaforwp_write_a_file($this->swjs_init, $swjsContent, $action);
                
        $swjsContent    = $this->fileCreation->pwaforwp_swr();
        $status         = pwaforwp_write_a_file($this->swr_init, $swjsContent, $action);
        
        /*$swjsContent    = '{"gcm_sender_id": "103953800507"}';
        $status         =  pwaforwp_write_a_file($this->firebase_manifest_init, $swjsContent, $action);*/
                         
        //Dummy file to work FCM perfectly 
        
        if($server_key !='' && $config !=''){

            $pn_sw_js       = $this->wppath."firebase-messaging-sw.js";  
            $swjsContent    = '';
            $status         =  pwaforwp_write_a_file($pn_sw_js, $swjsContent, $action);
        
        }
                
        return $status;
                                
    }    
}

add_action('wp_ajax_pwaforwp_download_setup_files', 'pwaforwp_download_setup_files');

function pwaforwp_download_setup_files(){   
    
    if ( ! isset( $_GET['pwaforwp_security_nonce'] ) ){
        return; 
    }
    if ( !wp_verify_nonce( $_GET['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
       return;  
    } 
    
    $file_type = sanitize_text_field($_GET['filetype']);    
    $file_creation_init_obj = new PWAFORWP_File_Creation_Init(); 
    $result = '';  
    
    switch($file_type){
        case 'pwa-sw':                
            $result = $file_creation_init_obj->pwaforwp_swjs_init(); 
            $result = $file_creation_init_obj->pwaforwp_swr_init(); 
            break;
        case 'pwa-manifest':                
            $result = $file_creation_init_obj->pwaforwp_manifest_init();  
            break;
        case 'pwa-amp-sw':                
           $result = $file_creation_init_obj->pwaforwp_swjs_init_amp();
           $result = $file_creation_init_obj->pwaforwp_swhtml_init_amp();
            break;
        case 'pwa-amp-manifest':               
           $result = $file_creation_init_obj->pwaforwp_manifest_init_amp();
            break;   
        default:
            //code
            break;
    }            
    if($result){
      echo json_encode(array('status'=>'t', 'message'=>esc_html__( 'File has been created', 'pwa-for-wp' )));  
    }else{
      echo json_encode(array('status'=>'f', 'message'=>esc_html__( 'Check permission or download from manual', 'pwa-for-wp' )));  
    }
    wp_die();           
}