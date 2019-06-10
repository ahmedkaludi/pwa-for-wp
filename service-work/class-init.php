<?php 
/**
 * 
 * Class PWAFORWP_File_Creation_Init
 *
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
        
        $multisite_filename_postfix = '';
        if ( is_multisite() ) {
           $multisite_filename_postfix = '-' . get_current_blog_id();
        }
        $this->wppath                 = str_replace("//","/",str_replace("\\","/",realpath(ABSPATH))."/"); 
        $this->fileCreation           = new pwaforwpFileCreation();
        $this->swjs_init              = $this->wppath.PWAFORWP_FILE_PREFIX."-sw".$multisite_filename_postfix.".js";
        $this->minifest_init          = $this->wppath.PWAFORWP_FILE_PREFIX."-manifest".$multisite_filename_postfix.".json";
        $this->swr_init               = $this->wppath.PWAFORWP_FILE_PREFIX."-register-sw".$multisite_filename_postfix.".js";
        $this->swjs_init_amp          = $this->wppath.PWAFORWP_FILE_PREFIX."-amp-sw".$multisite_filename_postfix.".js";
        $this->minifest_init_amp      = $this->wppath.PWAFORWP_FILE_PREFIX."-amp-manifest".$multisite_filename_postfix.".json";
        $this->swhtml_init_amp        = $this->wppath.PWAFORWP_FILE_PREFIX."-amp-sw".$multisite_filename_postfix.".html";
        $this->firebase_manifest_init = $this->wppath.PWAFORWP_FILE_PREFIX."-push-notification-manifest".$multisite_filename_postfix.".json";                         
        $this->push_notification_js   = PWAFORWP_PLUGIN_DIR.'/assets/'.PWAFORWP_FILE_PREFIX."-push-notification".$multisite_filename_postfix.".js";                         
    }

    
    public function pwaforwp_push_notification_js($js_str){
        $writestatus='';
		if(file_exists($this->push_notification_js)){
			unlink($this->push_notification_js);
		}
        if(!file_exists($this->push_notification_js)){
            $swjsContent = $js_str;
            $handle      = @fopen($this->push_notification_js, 'w');
            $writestatus = @fwrite($handle, $swjsContent);
            @fclose($handle);
        }                        
        if($writestatus){
            return true;   
        }else{
            return false;   
        }                        
    }
    
    
    public function pwaforwp_swjs_init(){
        $writestatus='';
		if(file_exists($this->swjs_init)){
			unlink($this->swjs_init);
		}
        if(!file_exists($this->swjs_init)){
            $swjsContent = $this->fileCreation->pwaforwp_swjs();
            $handle      = @fopen($this->swjs_init, 'w');
            $writestatus = @fwrite($handle, $swjsContent);
            @fclose($handle);
        }                        
        if($writestatus){
            return true;   
        }else{
            return false;   
        }                        
    }
    
    public function pwaforwp_manifest_init(){
        $writestatus = '';
        if(file_exists($this->minifest_init)){
            unlink($this->minifest_init);
        }
        if(!file_exists($this->minifest_init)){				
            $swHtmlContent  = $this->fileCreation->pwaforwp_manifest();
            $handleHtml     = @fopen($this->minifest_init, 'w');
            $swHtmlContent  = str_replace("&#038;", '&', $swHtmlContent);
            $writestatus    = @fwrite($handleHtml, $swHtmlContent );
            @fclose($handleHtml);
        }
        if($writestatus){
            return true;   
        }else{
            return false;   
        }
    }
    
    public function pwaforwp_swr_init(){   
        $writestatus = '';
        if(file_exists($this->swr_init)){
            unlink($this->swr_init);
        }
        if(!file_exists($this->swr_init)){
            $swjsContent    = $this->fileCreation->pwaforwp_swr();
            $handle         = @fopen($this->swr_init, 'w');
            $writestatus    = @fwrite($handle, $swjsContent);
            @fclose($handle);
        }
        if($writestatus){
            return true;   
        }else{
            return false;   
        }
    }
    
    public function pwaforwp_swjs_init_amp(){  
        $writestatus='';
        if(file_exists($this->swjs_init_amp)){
            unlink($this->swjs_init_amp);
        }
        if(!file_exists($this->swjs_init_amp)){
            $swjsContent    = $this->fileCreation->pwaforwp_swjs(true);
            $handle         = @fopen($this->swjs_init_amp, 'w');
            $writestatus    = @fwrite($handle, $swjsContent);
            @fclose($handle);
        }
        if( $writestatus ){
            return true;   
        }else{
            return false;   
        }
     }
     public function pwaforwp_manifest_init_amp(){
        $writestatus='';
        if(file_exists($this->minifest_init_amp)){
            unlink($this->minifest_init_amp);
        }
        if(!file_exists($this->minifest_init_amp)){				
            $swHtmlContent = $this->fileCreation->pwaforwp_manifest(true);
            $handleHtml = @fopen($this->minifest_init_amp, 'w');
            $writestatus = @fwrite($handleHtml, $swHtmlContent);
            @fclose($handleHtml);
        }
        if($writestatus){
            return true;   
        }else{
            return false;   
        }
    }    
    public function pwaforwp_swhtml_init_amp(){  
        $writestatus='';
        if(file_exists($this->swhtml_init_amp)){
            unlink($this->swhtml_init_amp);
        }
        if(!file_exists($this->swhtml_init_amp)){
            $swHtmlContent = $this->fileCreation->pwaforwp_swhtml(true);
            $handleHtml = @fopen($this->swhtml_init_amp, 'w');
            $writestatus = @fwrite($handleHtml, $swHtmlContent);
            @fclose($handleHtml);
        }

        if( $writestatus ){
            return true;   
        } else {
            return false;   
        }
    }
    public function pwaforwp_swhtml_init_firebase_js(){  
        $writestatus='';
        if(file_exists($this->swjs_init)){
			unlink($this->swjs_init);
	}
        if(!file_exists($this->swjs_init)){
            $swjsContent = $this->fileCreation->pwaforwp_swjs();
            $handle      = @fopen($this->swjs_init, 'w');
            $writestatus = @fwrite($handle, $swjsContent);
            @fclose($handle);
        }                             
        if(file_exists($this->swr_init)){
            unlink($this->swr_init);
        }
        if(!file_exists($this->swr_init)){
            $swjsContent    = $this->fileCreation->pwaforwp_swr();
            $handle         = @fopen($this->swr_init, 'w');
            $writestatus    = @fwrite($handle, $swjsContent);
            @fclose($handle);
        }
        if(file_exists($this->firebase_manifest_init)){
            unlink($this->firebase_manifest_init);
        }
        if(!file_exists($this->firebase_manifest_init)){
            $swjsContent    = '{"gcm_sender_id": "103953800507"}';
            $handle         = @fopen($this->firebase_manifest_init, 'w');
            $writestatus    = @fwrite($handle, $swjsContent);
            @fclose($handle);
        }        
        if($writestatus){
            return true;   
        }else{
            return false;   
        }        
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