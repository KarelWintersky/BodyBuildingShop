<?php
Class Front_Order_Delivery_Courier Extends Common_Rq{
	
	private $registry;
					
	public function __construct($registry){
		$this->registry = $registry;
	}	

	public function extra_fields(){
		return $this->do_rq('fields',NULL);	
	}	
	
	public function calculate_cost(){
		
		$index_arr = $this->registry['logic']->get_index_data(trim($this->registry['userdata']['zip_code']));
		
		$this->registry['delivery_cost_array'] = array(
				'cost' => ($index_arr['is_spb']==0 || $this->registry['full_cart_arr']['overall_price']>=FREE_DELIVERY_SUM) ? 0 : COURIER_SPB_COST,
				'is_spb' => $index_arr['is_spb'],
		);
	}
			
}
?>