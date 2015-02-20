<?php
Class Front_Order_Data_Discount{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;	
	}	
	
	private function calculate_discounts($data){
		$personal = ($this->registry['userdata'])
			? $this->registry['userdata']['personal_discount']
			: 0;
	
		$coupon = $this->registry['CL_storage']->get_storage('coupon_discount');
	
		/*
		 * скидка по купону всегда перебивает персональную, даже если меньше
		 * */
		
		return array(
				'personal' => $personal,
				'coupon' => $coupon,
				'sum' => ($coupon) ? $coupon : $personal 
		);
	}	
	
	public function get_discount($data){	
		$discounts = $this->calculate_discounts($data);
		
		$discount_sum = round($data['sum']*$discounts['sum']/100);
		$sum_with_discount = $data['sum'] - $discount_sum; 
		
		$params = array(
			'discounts' => $discounts,
			'discount_sum' => $discount_sum,
			'sum_with_discount' => $sum_with_discount,
			'coupon' => $this->registry['CL_storage']->get_storage('coupon')
			);
	
		return $data + $params;
	}
	
}
?>