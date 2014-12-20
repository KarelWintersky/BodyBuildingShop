<?php
Class Front_Catalog_Goods_List_Paginate{

	private $registry;
	private $values;
		
	public function __construct($registry){
		$this->registry = $registry;
		$this->registry->set('CL_catalog_paginate',$this);	
		
		$this->values = array(
			'list' => array(
					20 => '20 на стр.',
					50 => '50 на стр.',
					0 => 'все',
			),
			'table' => array(
					50 => '50 на стр.',
					0 => 'все',
			)
		);
		
	}
	
	public function get_current_paging($from){
		$type = Front_Catalog_Goods_List_Helper::get_type($from);
		$display_type = $this->registry['CL_catalog_display']->get_display_type($from);
		
		if(isset($_COOKIE[$this->registry['cookie_type']]['display_number'][$display_type][$this->registry[$type]['id']]))
			return $_COOKIE[$this->registry['cookie_type']]['display_number'][$display_type][$this->registry[$type]['id']];
		
		if($display_type=='list') return 20;
		else return 0;		
	}
	
	public function print_options($from){
		$display_type = $this->registry['CL_catalog_display']->get_display_type($from);
		$cur = $this->get_current_paging($from);
		
		$data = array();
		foreach($this->values[$display_type] as $key => $name)
			$data[] = array(
					'val' => $key,
					'name' => $name,
					'selected' => ($key==$cur)
					);
		
		return Front_Template_Select::opts($data);
	}
	
		
}
?>