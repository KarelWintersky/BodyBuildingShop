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
	
	private function do_text($method_id,$arr){
		if($method_id==1 && !$this->registry['userdata']) return false;
		
		return $arr['text'];
	}
	
	private function print_items($data){		
		$methods = $this->Front_Order_Delivery_Methods->get_actual_list();
		
		$html = array();
		foreach($methods as $method_id => $arr){
			
			$classname = __CLASS__.'_'.$arr['class_alias'];
			$CL = new $classname($this->registry); 
			
			$a = array(
					'name' => $arr['name'],
					'id' => $method_id,
					'checked' => ($arr['active']) ? 'checked' : '',
					'disabled' => ($arr['disabled']) ? 'disabled' : '',
					'classes' => $this->print_classes($arr),
					'cost' => $CL->calculate_cost($data),
					'text' => $this->do_text($method_id,$arr),
					'fields' => $CL->extra_fields()
					);
			
			$html[] = $this->do_rq('item',$a,true);
		}
		
		return implode('',$html);
	}
		
	public function do_vars(){
		$data = $this->registry['CL_data']->get_data();
		
		$vars = array(
				'crumbs' => $this->Front_Order_Crumbs->do_crumbs(2),
				'items' => $this->print_items($data)
		);
	
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
			
}
?>