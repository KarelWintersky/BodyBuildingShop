<?php
Class Front_Order_Delivery_Courier Extends Common_Rq{
	
	private $registry;
					
	public function __construct($registry){
		$this->registry = $registry;
	}	

	public function extra_fields(){
		return $this->do_rq('fields',NULL);	
	}	
	
	public function calculate_cost($data){
		$arr = $data['costs']['courier'];
				
		$a = array(
				'price' => Common_Useful::price2read($arr['sum']),
				'free_delivery_sum' => Common_Useful::price2read(FREE_DELIVERY_SUM),
				'free_delivery_diff' => Common_Useful::price2read(FREE_DELIVERY_SUM - $data['sum']),
				'is_spb' => $arr['is_spb'],
				'no_zip_code' => $arr['no_zip_code']
				);
		
		return $this->do_rq('cost',$a);
	}
			
}
?>