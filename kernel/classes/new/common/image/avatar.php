<?php
Class Common_Image_Avatar{

        private $registry;
        
        private $Common_Avatar;

        function __construct($registry) {
			$this->registry = $registry;
			
			$this->Common_Avatar = new Common_Avatar($this->registry);
        }

        private function avatar_check($path){
        	$qLnk = mysql_query(sprintf("
        			SELECT
        				COUNT(*)
        			FROM
        				%s
        			WHERE
        				parent_id = '%s'
        				AND
        				num = '%d'
        				AND
        				alias = '%s'
        			",
        			sprintf('%s_avatar',$path[0]),
        			$path[3],
        			$path[1],
        			mysql_real_escape_string($path[4])
        			));
        	return (mysql_result($qLnk,0)>0);
        }
        
        private function get_size($part,$parent_id,$num,$size_id,$size,$filename){
        	$src_path = $this->registry['config']['photo']['src'].'avatar/'.$part.'/'.$parent_id.'/'.$num.'/'.$filename;
        	$path = $this->registry['config']['photo']['size'].'avatar/'.$part.'/'; Common_Helper_Files::create_dir($path); 
        	
        	$path.=$parent_id.'/'; Common_Helper_Files::create_dir($path);
        	
        	$path.=$num.'/'; Common_Helper_Files::create_dir($path);
        	
        	$path.=$size_id.'/'; Common_Helper_Files::create_dir($path);
        	
        	$file = $path.$filename;
        	
        	if(!is_file($file)) $this->make_size($src_path,$file,$size);
        		
        	return $file;
        }
        
        private function make_size($src_path,$file,$size){
        	if(!is_file($src_path)) return false;
        	
        	Common_Image_Resize::do_resize(
        			$src_path,
        			$file,
        			$size[0],
        			$size[1],
        			$size[2]
        	);        	
        }
        
        public function get_image($path){
        	if(count($path)!=5) return false;
        	 
        	$part = $this->Common_Avatar->part_check($path[0]);
        	if(!$part) return false;
        	
        	$num = (isset($part[$path[1]])) ? $part[$path[1]] : false;
        	if(!$num) return false;
        	
        	$size = (isset($num['sizes'][$path[2]])) ? $num['sizes'][$path[2]] : false;
        	if(!$size) return false;
        	
        	/*
        	 * сначала проверяем в базе данных, есть ли запись о таком аватаре
        	 * затем првоеряем, есть ли конкретный размер
        	 * если есть, выводим, если нет - нарезаем и выводим
        	 * */
        	if(!$this->avatar_check($path)) return false;
        	
        	$img = $this->get_size($path[0],$path[3],$path[1],$path[2],$size,$path[4]);
        	
        	return $img;   	
        }
        
}
?>