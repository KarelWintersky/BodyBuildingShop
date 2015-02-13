<?php
Class Front_Order_Delivery_Post Extends Common_Rq{
	
	private $registry;
					
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	public function extra_fields(){
		
	}
	
	public function calculate_cost($data){
		$arr = $data['costs']['post'];
		
		$a = array(
				'price' => Common_Useful::price2read($arr['total_cost']),
				'is_spb' => $arr['is_spb'],
				);
		
		return $this->do_rq('cost',$a);
	}	

			
}
?>