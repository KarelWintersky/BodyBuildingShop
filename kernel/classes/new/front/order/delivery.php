<?php
Class Front_Order_Delivery Extends Common_Rq{

	private $registry;
	private $methods;
	
	private $Front_Order_Crumbs;
			
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Crumbs = new Front_Order_Crumbs($this->registry);
		
		$this->methods = array(
				1 => array(
					'name' => 'Доставка по почте',
					'payment' => array(1,2,3,4,6,7)
				),
				2 => array(
					'name' => 'Доставка курьером',
					'payment' => array(2,3,4,5,6,7)
				),
				/*3 => array(
				 	'name' => 'Доставка транспортной компанией',
				 	'payment' => array(2,3,4,6,7)
				),*/
				4 => array(
					'name' => 'Самовывоз',
					'payment' => array(2,3,4,5,6,7)
				),				
				);
	}	
		
	public function do_vars(){
		$vars = array(
				'crumbs' => $this->Front_Order_Crumbs->do_crumbs(2)
		);
	
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
			
}
?>