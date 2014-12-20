<?php
Class Front_Catalog_Goods_List_Display{

	private $registry;
	private $values;
		
	public function __construct($registry){
		$this->registry = $registry;
		$this->registry->set('CL_catalog_display',$this);	
		
		$this->values = array(
			'list' => 'Подробно',
			'table' => 'Списком'
		);
		
	}
	
	private function display_from_cookie($type){
		$display = (isset($_COOKIE[$this->registry['cookie_type']]['display_type'][$this->registry[$type]['id']]))
			? $_COOKIE[$this->registry['cookie_type']]['display_type'][$this->registry[$type]['id']]
			: 'list';
		
		return $display;
	}
	
	public function get_display_type($from){
		$type = Front_Catalog_Goods_List_Helper::get_type($from);

		$display = $this->display_from_cookie($type);
		
		return $display;
	}

	public function print_display_types($from){
		$html = array();
		
		$cur = $this->get_display_type($from);
		foreach($this->values as $id => $name)
			$html[] = sprintf('<li id="%s" class="%s" onclick="%s">%s</li>',
					$id,
					($cur==$id) ? 'active' : '',
					($cur!=$id) ? 'display_type_change(this);' : '',					
					$name
					);
		
		return implode('',$html);
	}
	
}
?>