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
				'price' => (isset($arr['cost'])) ? Common_Useful::price2read($arr['cost']) : false,
				'is_spb' => (isset($arr['is_spb'])) ? $arr['is_spb'] : false,
				'post_available' => (isset($arr['post_available'])) ? $arr['post_available'] : false,
				'zip_code_not_found' => (!isset($arr['cost'])),
				'no_zip_code' => $arr['no_zip_code'],
				);
		
		return $this->do_rq('cost',$a);
	}	

			
}
?>