<?php
Class Adm_Avatar_Convert{

        private $registry;
        private $part;
        
        private $Common_Avatar;
        
        function __construct($registry) {
			$this->registry = $registry;
			$this->part_alias = 'goods_vendors';
			
			$this->Common_Avatar = new Common_Avatar($this->registry);
        }

        private function get_data(){
        	$data = array();
        	$qLnk = mysql_query(sprintf("
        			SELECT
        				id, avatar
        			FROM
        				%s
        			WHERE
        				avatar <> ''
        			",
        			$this->part_alias
        			));
        	while($a = mysql_fetch_assoc($qLnk)) $data[$a['id']] = $a['avatar'];
        	
        	return $data;
        }
        
        private function make_src($copy_from_root,$parent_id,$filename,$size){
        	$filename = explode('.',$filename);
        	$ext = array_pop($filename);
        	$filename = implode('.',$filename);
        	
        	$filename = $filename.'_'.$size[0].'_'.$size[1].'.'.$ext;
        	
        	$src = $copy_from_root.$this->part_alias.'/'.$parent_id.'/';
        	
        	$file = $src.$filename;
        	
        	return $file;
        }
        
		public function do_convert(){
			$copy_from_root = ROOT_PATH.'public_html/data/photo/avatar/';
			$copy_to_root = ROOT_PATH.'data/images/avatar/';
			
			$data = $this->get_data();
			
			$path = $copy_to_root.$this->part_alias.'/';
			
			$part = $this->Common_Avatar->part_check($this->part_alias);
			
			foreach($data as $parent_id => $avatar){
				$this_path = $path.$parent_id.'/'; Common_Helper_Files::create_dir($this_path);
				
				foreach($part as $num => $arr){
					$num_path = $this_path.$num.'/'; Common_Helper_Files::create_dir($num_path);

					$file_dst = $num_path.$avatar;
					
					foreach($arr['sizes'] as $size_id => $size){
						$file_src = $this->make_src($copy_from_root,$parent_id,$avatar,$size);
						
						copy($file_src,$file_dst);
												
						break;
					}
					
					mysql_query(sprintf("
							INSERT INTO
								%s
								(parent_id, num, alias)
								VALUES
								('%s', '%d', '%s')
							",
							$this->part_alias.'_avatar',
							$parent_id, $num, $avatar
							));
					echo mysql_affected_rows();
				}
				
			}
		}
}
?>