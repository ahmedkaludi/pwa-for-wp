<?php 
/**
 * 
 * Class file_creation_init
 *
 */
class file_creation_init {
     public $wppath;
     public $fileCreation;
     public $swjs_init;
     public $minifest_init;
     public $swr_init;
     
     public $swjs_init_amp;
     public $minifest_init_amp;
     public $swhtml_init_amp;   
             
     public function __construct(){
        $this->wppath = str_replace("//","/",str_replace("\\","/",realpath(ABSPATH))."/"); 
        $this->fileCreation = new pwaforwpFileCreation(); 
                                        
        $this->swjs_init = $this->wppath.PWAFORWP_FRONT_FILE_PREFIX."-sw.js";       
        $this->minifest_init = $this->wppath.PWAFORWP_FRONT_FILE_PREFIX."-manifest.json";
        $this->swr_init = $this->wppath.PWAFORWP_FRONT_FILE_PREFIX."-register-sw.js";
        
        $this->swjs_init_amp = $this->wppath.PWAFORWP_FRONT_FILE_PREFIX."-amp-sw.js";       
        $this->minifest_init_amp = $this->wppath.PWAFORWP_FRONT_FILE_PREFIX."-amp-manifest.json";
        $this->swhtml_init_amp = $this->wppath.PWAFORWP_FRONT_FILE_PREFIX."-amp-sw.html";
        
     }
     public function pwaforwp_swjs_init(){                                 
			if(file_exists($this->swjs_init)){
				unlink($this->swjs_init);
			}
			if(!file_exists($this->swjs_init)){
				$swjsContent = $this->fileCreation->pwaforwp_swjs();                                 
				$handle = fopen($this->swjs_init, 'w');
				fwrite($handle, $swjsContent);
				fclose($handle);
			}
                        return true;
     }
     public function pwaforwp_manifest_init(){
         
			if(file_exists($this->minifest_init)){
				unlink($this->minifest_init);
			}
			if(!file_exists($this->minifest_init)){				
				$swHtmlContent = $this->fileCreation->pwaforwp_manifest();
				$handleHtml = fopen($this->minifest_init, 'w');
				fwrite($handleHtml, $swHtmlContent);
				fclose($handleHtml);
			}
                        return true;
     }
     public function pwaforwp_swr_init(){                                 
			if(file_exists($this->swr_init)){
				unlink($this->swr_init);
			}
			if(!file_exists($this->swr_init)){
				$swjsContent = $this->fileCreation->pwaforwp_swr();                                 
				$handle = fopen($this->swr_init, 'w');
				fwrite($handle, $swjsContent);
				fclose($handle);
			}
                        return true;
     }
     public function pwaforwp_swjs_init_amp(){                                 
			if(file_exists($this->swjs_init_amp)){
				unlink($this->swjs_init_amp);
			}
			if(!file_exists($this->swjs_init_amp)){
				$swjsContent = $this->fileCreation->pwaforwp_swjs(true);                                 
				$handle = fopen($this->swjs_init_amp, 'w');                              
				fwrite($handle, $swjsContent);
				fclose($handle);
			}
                        return true;
     }
     public function pwaforwp_manifest_init_amp(){
         
			if(file_exists($this->minifest_init_amp)){
				unlink($this->minifest_init_amp);
			}
			if(!file_exists($this->minifest_init_amp)){				
				$swHtmlContent = $this->fileCreation->pwaforwp_manifest(true);
				$handleHtml = fopen($this->minifest_init_amp, 'w');
				fwrite($handleHtml, $swHtmlContent);
				fclose($handleHtml);
			}
                        return true;
     }
     public function pwaforwp_swhtml_init_amp(){          
			if(file_exists($this->swhtml_init_amp)){
				unlink($this->swhtml_init_amp);
			}
			if(!file_exists($this->swhtml_init_amp)){                             
                                $swHtmlContent = $this->fileCreation->pwaforwp_swhtml(true);
				$handleHtml = fopen($this->swhtml_init_amp, 'w');
				fwrite($handleHtml, $swHtmlContent);
				fclose($handleHtml);                                				
			}
                        return true;
            }
    
}
add_action('wp_ajax_download_setup_files', 'download_setup_files');

function download_setup_files(){           
    $file_type = sanitize_text_field($_GET['filetype']);
    $result = '';        
    switch($file_type){
            case 'pwa-sw':
                $file_creation_init_obj = new file_creation_init(); 
                $result = $file_creation_init_obj->pwaforwp_swjs_init(); 
                $result = $file_creation_init_obj->pwaforwp_swr_init(); 
                break;
            case 'pwa-manifest':
                $file_creation_init_obj = new file_creation_init(); 
                $result = $file_creation_init_obj->pwaforwp_manifest_init();  
                break;
            case 'pwa-amp-sw':
                $file_creation_init_obj = new file_creation_init(); 
               $result = $file_creation_init_obj->pwaforwp_swjs_init_amp();
               $result = $file_creation_init_obj->pwaforwp_swhtml_init_amp();
                break;
            case 'pwa-amp-manifest':
                $file_creation_init_obj = new file_creation_init(); 
               $result = $file_creation_init_obj->pwaforwp_manifest_init_amp();
                break;   
            default:
                //code
                break;
        }
            if($result){
              echo json_encode(array('status'=>'t', 'message'=>'file created'));  
            }else{
              echo json_encode(array('status'=>'f', 'message'=>'Something went wrong'));  
            }
           wp_die();           
}