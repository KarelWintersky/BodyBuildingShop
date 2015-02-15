<?php
Class Front_Order_Payment Extends Common_Rq{

	private $registry;
	
	private $Front_Order_Crumbs;
	private $Front_Order_Payment_Methods;
			
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Crumbs = new Front_Order_Crumbs($this->registry);
		$this->Front_Order_Payment_Methods = new Front_Order_Payment_Methods($this->registry);
	}	
		
	private function print_classes($data){
		$classes = array();
	
		$classes[] = 'fop_item';
		if($data['disabled']) $classes[] = 'disabled';
	
		return implode(' ',$classes);
	}	
	
	private function print_items(){
		$methods = $this->Front_Order_Payment_Methods->get_actual_list();
		
		$html = array();
		foreach($methods as $method_id => $arr){
				
			$a = array(
					'name' => $arr['name'],
					'id' => $method_id,
					'checked' => ($arr['active']) ? 'checked' : '',
					'classes' => $this->print_classes($arr),
					'text' => $arr['text']
			);
				
			$html[] = $this->do_rq('item',$a,true);
		}
	
		return implode('',$html);
	}	
	
	public function do_vars(){
		$vars = array(
				'crumbs' => $this->Front_Order_Crumbs->do_crumbs(3),
				'items' => $this->print_items()
		);
	
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
			
}
?>