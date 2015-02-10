<?php
Class Front_Order_Cart{

	private $registry;
	
	private $Front_Order_Cart_Table;
	private $Front_Order_Cart_Coupon;
	private $Front_Order_Cart_Gift;
	private $Front_Order_Crumbs;
		
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Cart_Table = new Front_Order_Cart_Table($this->registry);
		$this->Front_Order_Cart_Coupon = new Front_Order_Cart_Coupon($this->registry);
		$this->Front_Order_Cart_Gift = new Front_Order_Cart_Gift($this->registry);
		$this->Front_Order_Crumbs = new Front_Order_Crumbs($this->registry);
	}	
		
	public function do_vars(){
		$vars = array(
				'table' => $this->Front_Order_Cart_Table->do_table(),	
				'coupon' => $this->Front_Order_Cart_Coupon->do_block(),		
				'gift' => $this->Front_Order_Cart_Gift->do_block(),		
				'crumbs' => $this->Front_Order_Crumbs->do_crumbs(1)					
				);
		
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
	
}
?>