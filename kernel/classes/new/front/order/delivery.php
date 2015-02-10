<?php
Class Front_Order_Delivery Extends Common_Rq{

	private $registry;
	
	private $Front_Order_Crumbs;
	private $Front_Order_Storage;
			
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Crumbs = new Front_Order_Crumbs($this->registry);
		$this->Front_Order_Storage = new Front_Order_Storage($this->registry);
	}	
		
	private function print_items(){
		$active = $this->Front_Order_Storage->get_storage('delivery');
			$active = ($active) ? $active : 1;
		
		$methods = Front_Order_Data_Delivery::get_methods();
		
		$html = array();
		foreach($methods as $method_id => $data){
			
			$a = array(
					'name' => $data['name'],
					'id' => $method_id,
					'checked' => ($method_id==$active) ? 'checked' : ''
					);
			
			$html[] = $this->do_rq('item',$a,true);
		}
		
		return implode('',$html);
	}
		
	public function do_vars(){
		$vars = array(
				'crumbs' => $this->Front_Order_Crumbs->do_crumbs(2),
				'items' => $this->print_items()
		);
	
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
			
}
?>