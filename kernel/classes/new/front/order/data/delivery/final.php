<?php
Class Front_Order_Data_Delivery_Final{

	/*
	 * расчет финальной суммы доставки (когда уже извествен способ доставки)
	 * */
	
	public function __construct($registry){
		$this->registry = $registry;
	}
			
	public function calculate_sum($data){
		$delivery = $this->registry['CL_storage']->get_storage('delivery');
		if(!$delivery) return $data;
		
		if($delivery==1){
			$sum = (isset($data['costs']['post']['cost'])) ? $data['costs']['post']['cost'] : 0;
		}elseif($delivery==2){
			$sum = $data['costs']['courier']['sum'];
		}elseif($delivery==4){
			$sum = 0;
		}
		
		$data['delivery_sum'] = $sum; 
		
		return $data;
	}	
}
?>