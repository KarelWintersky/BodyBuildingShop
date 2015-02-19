<?php
Class Front_Order_Write_Input{
	
	/*
	 * "выходные" данные - то, что в корзине и вообще человек указывал в процессе заказа
	 * */	
	
	private $registry;
	
	private $Front_Order_Storage;
	
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Storage = new Front_Order_Storage($this->registry);
	}	
			
	private function get_phone($deilvery){
		if($deilvery==1) return false;
		
		if($deilvery==2) return $this->Front_Order_Storage->get_storage('courier_phone');
		
		if($deilvery==4) return $this->Front_Order_Storage->get_storage('self_phone');
		
		return false;
	}
		
	public function make_data($data){	
		$deilvery = $this->Front_Order_Storage->get_storage('payment');
		$payment = $this->Front_Order_Storage->get_storage('payment');
		
		$input = array(
				'wishes' => $_POST['wishes'],
				'payment_method' => $payment,
				'delivery_type' => $deilvery,
				'coupon' => $this->Front_Order_Storage->get_storage('coupon'),
				'phone' => $this->get_phone($deilvery),
				'sum_with_discount' => $data['sum_with_discount'],
				'coupon_discount' => $this->Front_Order_Storage->get_storage('coupon_discount'),
				'delivery_costs' => ($deilvery==1 && $payment==1) 
					? $data['nalog'] + $data['delivery_sum']
					: $data['delivery_sum'],
				'overall_price' => ($deilvery==1 && $payment==1) 
					? $data['sum_with_discount'] + $data['nalog'] + $data['delivery_sum']
					: $data['sum_with_discount'] + $data['delivery_sum'],
				);
		
		return $input;
	}
}
?>