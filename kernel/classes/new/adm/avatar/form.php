<?php
Class Adm_Avatar_Form Extends Common_Rq{

        private $registry;
        private $part_alias;

        function __construct($registry,$part) {
			$this->registry = $registry;
			$this->registry->set('CL_avatar_form',$this);
			
			$this->part_alias = $part;
        }

        private function get_data($parent_id){
        	if(!$parent_id) return false;
        	
        	$images = array();
        	$qLnk = mysql_query(sprintf("
        			SELECT
        				*
        			FROM
        				%s
        			WHERE
        				parent_id = '%s'
        			",
        			$this->part_alias.'_avatar',
        			$parent_id
        			));
        	while($i = mysql_fetch_assoc($qLnk)) $images[$i['num']] = $i['alias'];
        	
        	return $images;
        }
        
        private function make_image($images,$num,$parent_id,$part){
        	if(!$images || !isset($images[$num])) return false;
        	
        	foreach($part['sizes'] as $size_id => $measures) break;
        	
        	return sprintf('/image/avatar/%s/%d/%d/%s/%s',
        			$this->part_alias,
        			$num,
        			$size_id,
        			$parent_id,
        			$images[$num]
        			);
        }
        
        public function print_forms($parent){
        	$parent_id = ($parent) ? $parent['id'] : false;
        	
        	$images = $this->get_data($parent_id);
        	$part = $this->registry['CL_avatar_logic']->part_check($this->part_alias);
        	
        	$html = array();
        	foreach($part as $num => $arr){
        		
        		$a = array(
        				'label' => (count($part)>1) ? sprintf('Изображение %d',$num) : 'Изображение',
        				'num' => $num,
        				'comment' => $arr['comment'],
        				'image' => $this->make_image($images,$num,$parent_id,$arr),
        				'alias' => ($images && isset($images[$num])) ? $images[$num] : false
        				);
        		
        		$html[] = $this->do_rq('avatar',$a,true);
        	}
        	
        	return $this->do_rq('storage',implode('',$html));
        }
        

}
?>