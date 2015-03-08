<?php
Class Common_Image{

        private $registry;
        
        private $Common_Image_Avatar;

        function __construct($registry) {
			$this->registry = $registry;
			
			$this->Common_Image_Avatar = new Common_Image_Avatar($this->registry);
        }
                
        private function throw_404(){
        	header("HTTP/1.0 404 Not Found");
        	die('Изображение не найдено');
        }
        
        private function image_to_screen($img){
        	$finfo = finfo_open(FILEINFO_MIME_TYPE);
        	$mime = finfo_file($finfo, $img);
        	finfo_close($finfo);     	
        	
        	header('Content-Type: '.$mime);
        	readfile($img);
        	exit();        	
        }
        
        public function resolve_path($path){
        	if($this->registry['config']['disable_images']) exit();
        	
        	$func = array_shift($path);
        	
        	$img = false;
        	
        	if($func=='avatar') $img = $this->Common_Image_Avatar->get_image($path);
        	
        	if(!$img) $this->throw_404();
        	else $this->image_to_screen($img);
        }

}
?>