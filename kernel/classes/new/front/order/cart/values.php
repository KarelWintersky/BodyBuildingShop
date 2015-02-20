<?php
Class Front_Order_Cart_Values Extends Common_Rq{

	private $registry;
	
	private $Front_Order_Cart_Values_Extra;
		
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Cart_Values_Extra = new Front_Order_Cart_Values_Extra($this->registry);
	}	
			
	private function discounts_string($discounts){
		$string = array();
		
		if($discounts['coupon']) return sprintf('%s %%',$discounts['coupon']);
		if($discounts['personal']) return sprintf('%s %%',$discounts['personal']);
		
		return false;
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
	
	private function print_overall($data,$check_step){
		if(!$check_step) return false;
	
		$payment = $this->registry['CL_storage']->get_storage('payment');
		$payment_sum = ($payment==1) ? $data['nalog'] : 0;
				
		$sum = $data['sum_with_discount'] + $data['delivery_sum'] + $payment_sum;
		
		$a = array(
				'sum' => Common_Useful::price2read($sum)  
				);
		
		return $this->do_rq('overall',$a);
	}	
	
	public function do_block($data,$check_step = false){

		$a = array(
				'discount' => $this->discount_block($data),
				'final_sum' => Common_Useful::price2read($data['sum_with_discount']),
				'final_sum_label' => (!$check_step)
					? 'Итоговая стоимость вашего заказа<br>(без учета доставки)'
					: 'Общая стоимость со скидкой',
				'final_sum_id' => (!$check_step)
					? 'ogv_3'
					: '',				
				'extra' => $this->Front_Order_Cart_Values_Extra->print_extra($data,$check_step),
				'overall' => $this->print_overall($data,$check_step),
				);
		
		return $this->do_rq('values',$a);
	}
	
}
?>