<?php
Class Front_Order_Delivery_Post Extends Common_Rq{
	
	private $registry;
					
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	public function extra_fields(){ return false; }
	
	public function calculate_cost($data){
		if(!$this->registry['userdata']) return $this->do_rq('closed',NULL);
		
		$arr = $data['costs']['post'];
		
		$a = array(
				'price' => Common_Useful::price2read($arr['cost']),
				'total_cost' => Common_Useful::price2read($arr['total_cost']),
				'hard_cost' => Common_Useful::price2read($arr['hard_cost']),
				'is_spb' => $arr['is_spb'],
				);
		
		return $this->do_rq('cost',$a);
	}	

			
}
?>