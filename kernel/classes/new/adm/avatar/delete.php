<?php
Class Adm_Avatar_Delete{

        private $registry;
        private $path;
        private $part_alias;

        function __construct($registry,$path,$part_alias) {
			$this->registry = $registry;
			$this->registry->set('CL_avatar_delete',$this);
			
			$this->path = $path;
			$this->part_alias = $part_alias;
        }
                
        public function delete_all($parent_id){
        	$items = array();
        	$qLnk = mysql_query(sprintf("
        			SELECT
        				*
        			FROM
        				%s
        			WHERE
        				parent_id = '%s'
        			",
        			$this->part_alias."_avatar",
        			$parent_id
        			));
        	while($p = mysql_fetch_assoc($qLnk)) $items[] = $p;
        	
        	foreach($items as $p) $this->delete_one($parent_id,$p['num'],$p['alias']);
        }
        
        public function do_delete($parent_id){
			if(!isset($_POST['avatar'])) return false;
        	
        	foreach($_POST['avatar'] as $num => $arr)
        		if(isset($arr['delete'])) 
        			$this->delete_one($parent_id,$num,$arr['alias']);
        }
        
        private function delete_one($parent_id,$num,$alias){
        	$this->delete_src($parent_id,$num,$alias);
        	$this->delete_sizes($this->part_alias,$parent_id,$num);
        	
        	mysql_query(sprintf("
        			DELETE FROM
        				%s
        			WHERE
        				parent_id = '%s'
        				AND
        				num = '%d'
        			",
        			$this->part_alias."_avatar",
        			$parent_id,
        			$num
        	));        	
        }

        private function delete_src($parent_id,$num,$alias){
        	$parent_folder = $this->path.$parent_id.'/';
        	
        	$num_folder = $parent_folder.$num.'/';
        	
        	$file = $num_folder.$alias;

        	if(is_file($file)) unlink($file);
        	Common_Helper_Files::delete_dir_if_empty($num_folder);
        	Common_Helper_Files::delete_dir_if_empty($parent_folder);
        }
        
        private function delete_sizes($part_alias,$parent_id,$num){
        	$root_dir = $this->registry['config']['photo']['size'].'avatar/'.$part_alias.'/'.$parent_id.'/';
        	
        	$num_dir = $root_dir.$num.'/';
        	
        	Common_Helper_Files::clear_dir($num_dir);
        	Common_Helper_Files::delete_dir_if_empty($num_dir);
        	Common_Helper_Files::delete_dir_if_empty($root_dir);
        }      
        
}
?>