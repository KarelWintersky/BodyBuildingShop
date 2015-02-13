<?php
Class Front_Order_Delivery Extends Common_Rq{

	private $registry;
	
	private $Front_Order_Crumbs;
	private $Front_Order_Storage;
	private $Front_Order_Delivery_Methods;
				
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Crumbs = new Front_Order_Crumbs($this->registry);
		$this->Front_Order_Storage = new Front_Order_Storage($this->registry);
		$this->Front_Order_Delivery_Methods = new Front_Order_Delivery_Methods($this->registry);
	}	
		
	private function print_classes($data){
		$classes = array();
		
		$classes[] = 'fod_item';
		if($data['disabled']) $classes[] = 'disabled';
		
		return implode(' ',$classes);
	}
	
	private function print_items(){		
		$methods = $this->Front_Order_Delivery_Methods->get_actual_list();
		
		$html = array();
		foreach($methods as $method_id => $data){
			
			$classname = __CLASS__.'_'.$data['class_alias'];
			$CL = new $classname($this->registry); 
			
			$a = array(
					'name' => $data['name'],
					'id' => $method_id,
					'checked' => ($data['active']) ? 'checked' : '',
					'disabled' => ($data['disabled']) ? 'disabled' : '',
					'classes' => $this->print_classes($data),
					'price' => '300 руб.',
					'text' => $data['text'],
					'fields' => $CL->extra_fields()
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