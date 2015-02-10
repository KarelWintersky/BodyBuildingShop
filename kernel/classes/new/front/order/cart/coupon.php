<?php
Class Front_Order_Cart_Coupon Extends Common_Rq{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	public function do_block(){
		return $this->do_rq('coupon',NULL);
	}
	
}
?>