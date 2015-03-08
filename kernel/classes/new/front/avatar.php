<?php

Class Front_Avatar{

		private $registry;
		private $part_alias;

        function __construct($registry,$part_alias) {
          $this->registry = $registry;
          
          $this->part_alias = $part_alias;
        }

        private function get_data($num,$keys){
        	foreach($keys as $k => $v) $keys[$k] = sprintf("'%s'",$v);
        	
        	$avatars = array();
        	$qLnk = mysql_query(sprintf("
        			SELECT
        				*
        			FROM
        				%s_avatar
        			WHERE
        				num = '%d'
        				AND
        				parent_id IN (%s)
        			",
        			$this->part_alias,
        			$num,
        			implode(",",$keys)
        			));
        	while($a = mysql_fetch_assoc($qLnk)) $avatars[$a['parent_id']] = $a['alias']; 
        	
        	return $avatars;
        }
        
        private function construct_url($num,$size_id,$parent_id,$alias){
        	return sprintf('/image/avatar/%s/%d/%d/%s/%s',
        			$this->part_alias,
        			$num,
        			$size_id,
        			$parent_id,
        			$alias
        	);
        }
        
        public function single_avatar($id,$num,$size_id){
        	$avatar = $this->get_data($num,array($id));
        	if(!count($avatar)) return false;
        	        	
        	return $this->construct_url($num,$size_id,$id,$avatar[$id]);
        }
        
		public function list_avatars($list,$num,$size_id){
			if(!count($list)) return $list;
			
			$avatars = $this->get_data($num,array_keys($list));
			
			foreach($avatars as $key => $val) 
				$avatars[$key] = $this->construct_url($num,$size_id,$key,$val);
			
			foreach($list as $id => $arr)
				$list[$id]['avatar'] = (isset($avatars[$id])) ? $avatars[$id] : false;
			
			return $list;
		}

}
?>