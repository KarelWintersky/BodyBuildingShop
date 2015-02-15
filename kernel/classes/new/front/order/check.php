<?php
Class Front_Order_Check Extends Common_Rq{

	private $registry;
	
	private $Front_Order_Crumbs;
	private $Front_Order_Cart_Table;
	private $Front_Order_Cart_Values;
			
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Crumbs = new Front_Order_Crumbs($this->registry);
		$this->Front_Order_Cart_Table = new Front_Order_Cart_Table($this->registry);
		$this->Front_Order_Cart_Values = new Front_Order_Cart_Values($this->registry);
	}	
		
	private function delivery_payment($data){
		return $this->do_rq('delivery_payment',NULL);
	}
	
	private function wishes(){
		return $this->do_rq('wishes',NULL);
	}
	
	public function do_vars(){
		$data = $this->registry['CL_data']->get_data();
		
		$vars = array(
			'crumbs' => $this->Front_Order_Crumbs->do_crumbs(4),
			'table' => $this->Front_Order_Cart_Table->do_table($data,true),
			'values' => $this->Front_Order_Cart_Values->do_block($data),
			'delivery_payment' => $this->delivery_payment($data),
			'wishes' => $this->wishes()
		);
	
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
			
}
?>