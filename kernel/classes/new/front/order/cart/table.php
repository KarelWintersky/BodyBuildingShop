<?php
Class Front_Order_Cart_Table Extends Common_Rq{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	private function print_lines($data,$readonly){
		$html = array();
				
		$goods = $data['goods'];
		
		if($readonly && $data['gift']) $goods[] = $data['gift'];
			
		foreach($goods as $key => $g){
			$g['url'] = (isset($g['parent_alias']))
				? sprintf('/%s/%s/%s/',
						$g['parent_alias'],
						$g['level_alias'],
						$g['alias']
						)
				: false;
			$g['name_print'] = (isset($g['grower_id']) && $g['grower_id'])
				? sprintf('«%s». %s',$g['grower_name'],$g['name'])
				: $g['name'];
			
			$g['features_colors'] = $this->features_colors($g);
			
			$g['key'] = $key;
			
			$g['readonly'] = $readonly;
						
			$html[] = $this->do_rq('line',$g,true);
		}
				
		return implode('',$html);
	}
	
	private function features_colors($g){
		if(!isset($g['feature']) && !isset($g['color'])) return false;
		if(!$g['feature'] && !$g['color']) return false;
		
		$label = ($g['root_id']==4) ? 'Размер' : 'Вкус';

		$string = array();
			$string[] = (strpos($g['feature'],$label)===false) 
				? sprintf('%s: %s',$label,$g['feature'])
				: $g['feature'];
				
			if($g['color']) $string[] = sprintf('цвет: %s',$g['color_name']);
		
		return implode(', ',$string);
	}
	
	public function do_table($data,$readonly = false){
		
		$a = array(
				'lines' => $this->print_lines($data,$readonly),
				'readonly' => $readonly
				);
		
		return $this->do_rq('table',$a);
	}
	
}
?>