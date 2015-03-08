<?php
Class Adm_Avatar_Upload{

        private $registry;
        private $path;
        
        private $Adm_Avatar_Delete;

        function __construct($registry,$part) {
			$this->registry = $registry;
			$this->registry->set('CL_avatar_upload',$this);
			
			$this->part_alias = $part;
			$this->path = $this->registry['config']['photo']['src'].'avatar/'.$this->part_alias.'/';
			
			$this->Adm_Avatar_Delete = new Adm_Avatar_Delete($this->registry,$this->path,$this->part_alias);
        }
                
        private function process_file($num,$arr,$path){
        	$path.=$num.'/'; Common_Helper_Files::create_dir($path);
        	
        	Common_Helper_Files::clear_dir($path);
        	
        	$name = Common_Helper_Files::filename2translit($arr['name']);
        	$file = $path.$name;
        	
        	return (move_uploaded_file($arr['tmp_name'],$file)) ? $name : false;
        }
        
        private function write_to_base($parent_id,$num,$alias){
        	$table = $this->part_alias.'_avatar';
        	
        	mysql_query(sprintf("
        			DELETE FROM
        				%s
        			WHERE
        				parent_id = '%s'
        				AND
        				num = '%d'
        			",
        			$table,
        			$parent_id,
        			$num
        			));
        	
        	mysql_query(sprintf("
        			INSERT INTO
        				%s
        				(parent_id, num, alias)
        				VALUES
        				('%s', '%d', '%s')
        			",
        			$table,
        			$parent_id,$num,$alias
        			));
        }
        
        public function upload_avatars($parent_id){
        	$this->Adm_Avatar_Delete->do_delete($parent_id);
        	
        	if(!isset($_FILES['avatars'])) return false;
        	
        	$files = Adm_Avatar_Upload_Prepare::prepare_files_array($_FILES['avatars']);
        	
        	foreach($files as $num => $arr)
        		if(!$arr['name'] || !$arr['type'] || !$arr['tmp_name']  || !$arr['size']  || $arr['error'])
        			unset($files[$num]);
        	
        	Common_Helper_Files::create_dir($this->path);
        	
        	$path = $this->path.$parent_id.'/'; Common_Helper_Files::create_dir($path);
        	
        	foreach($files as $num => $arr){
        		$alias = $this->process_file($num,$arr,$path);
        		if($alias) $this->write_to_base($parent_id,$num,$alias);
        	}
        	
        }        

}
?>