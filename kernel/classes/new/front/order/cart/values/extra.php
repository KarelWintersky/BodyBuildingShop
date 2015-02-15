<?php
Class Front_Order_Cart_Values_Extra Extends Common_Rq{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
				
	public function print_extra($data,$check_step){
		if(!$check_step) return false;
		
		$payment = $this->registry['CL_storage']->get_storage('payment');
		
		$a = array(
				'nalog' => ($payment==1) 
					? Common_Useful::price2read($data['nalog']) 
					: false,
				'free_delivery' => ($data['delivery_sum']===0),
				'delivery' => Common_Useful::price2read($data['delivery_sum'])
				);
				
		return $this->do_rq('extra',$a);
	}

	
}
?>