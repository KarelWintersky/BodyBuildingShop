<?php
Class Front_Order_Cart_Values Extends Common_Rq{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	private function discounts_string($discounts){
		$string = array();
		
		if($discounts['personal']) $string[] = sprintf('%s %%',$discounts['personal']);
		if($discounts['coupon']) $string[] = sprintf('%s %%',$discounts['coupon']);
		
		return implode(' + ',$string);
	}
	
	private function discount_block($data){
		if(!$data['discounts']['sum']) return false;
		
		$a = array(
				'without_discount' => Common_Useful::price2read($data['sum']),
				'discount_sum' => Common_Useful::price2read($data['discount_sum']),
				'discounts_string' => $this->discounts_string($data['discounts'])
				);
		
		return $this->do_rq('discount',$a);
	}
	
	public function do_block($data){

		$a = array(
				'discount' => $this->discount_block($data),
				'final_sum' => Common_Useful::price2read($data['sum_with_discount'])
				);
		
		return $this->do_rq('values',$a);
	}
	
}
?>