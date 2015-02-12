<?php
Class Front_Order_Cart_Coupon Extends Common_Rq{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	public function do_block($data){
		$a = array(
				'coupon' => ($data['discounts']['coupon']) ? $data['coupon'] : false 
				);
		
		return $this->do_rq('coupon',$a);
	}
	
	private function get_discount($coupon){
		if(!$coupon || mb_strlen($coupon,'utf-8')!=5) return false;

		$qLnk = mysql_query(sprintf("
				SELECT
					percent
				FROM
					coupons
				WHERE
					hash = '%s'
					AND
					status = '1'
				LIMIT 1;
				",
				mysql_real_escape_string($coupon)
				));
		$discount = mysql_fetch_assoc($qLnk);
		if(!$discount) return false;
		
		return $discount['percent'];
	}
	
	public function apply_coupon($coupon){
		$Front_Order_Storage = new Front_Order_Storage($this->registry);
		$Front_Order_Data = new Front_Order_Data($this->registry);
		$Front_Order_Cart_Values = new Front_Order_Cart_Values($this->registry);
		
		$discount = $this->get_discount($coupon);
		
		$Front_Order_Storage->write_to_storage('coupon',$coupon);
		$Front_Order_Storage->write_to_storage('coupon_discount',$discount);
		
		$data = $Front_Order_Data->get_data();
		
		echo $Front_Order_Cart_Values->do_block($data);
	}
	
}
?>