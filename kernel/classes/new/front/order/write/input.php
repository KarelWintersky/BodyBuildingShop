<?php
Class Front_Order_Write_Input{
	
	/*
	 * "выходные" данные - то, что в корзине и вообще человек указывал в процессе заказа
	 * */	
	
	private $registry;
	
	private $Front_Order_Storage;
	private $Front_Order_Data;
	
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Storage = new Front_Order_Storage($this->registry);
		$this->Front_Order_Data = new Front_Order_Data($this->registry);
	}	
			
	private function get_phone($deilvery){
		if($deilvery==1) return false;
		
		if($deilvery==2) return $this->Front_Order_Storage->get_storage('courier_phone');
		
		if($deilvery==4) return $this->Front_Order_Storage->get_storage('self_phone');
		
		return false;
	}
	
	public function make_data(){
		$data = $this->Front_Order_Data->get_data();
				
		$deilvery = $this->Front_Order_Storage->get_storage('payment');
		
		$input = array(
				'wishes' => $_POST['wishes'],
				'payment_method' => $this->Front_Order_Storage->get_storage('payment'),
				'delivery_type' => $deilvery,
				'coupon' => $this->Front_Order_Storage->get_storage('coupon'),
				'phone' => $this->get_phone($deilvery),
				'sum_with_discount' => $data['sum_with_discount'],
				'coupon_discount' => '',
				'delivery_costs' => '',
				'overall_price' => '',
				);
		
		return $input;
	}
}
?>