<?php
Class Front_Order_Payment_Methods{

	/*
	 * тут формируется список способов оплаты
	* в зависимости от уровня авторизации покупателя
	* и выбранного способа доставки
	* */	
	
	private $registry;
	
	private $Front_Order_Storage;
				
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Storage = new Front_Order_Storage($this->registry);
	}	
		
	private function get_delivery_match(){
		$active = $this->Front_Order_Storage->get_storage('delivery');
		$delivery = Front_Order_Data_Delivery::get_methods($active);
		
		return $delivery['payment'];
	}
	
	private function correct_list($list,$data){
		foreach($list as $id => $arr) if($arr['disabled']) $list[$id]['active'] = false;		
		
		//закрываем наложку, если есть соответствующий параметр
		if(!$data['nalog_payment_available']) $list[1]['disabled'] = true; $list[1]['active'] = false;
		
		$is_active = false;
		foreach($list as $l) if($l['active']) $is_active = true;
	
		if(!$is_active)
			foreach($list as $id => $arr){ 
				if(!$arr['disabled']){ 
					$list[$id]['active'] = true;
					break;
				}
			}
		
		return $list;
	}	
	
	public function get_actual_list($data){
		$active = $this->Front_Order_Storage->get_storage('payment');
			$active = ($active) ? $active : 1;
		
		$match = $this->get_delivery_match();
		
		$methods = Front_Order_Data_Payment::get_methods();

		$texts = Front_Order_Helper::delivery_payment_texts($methods);
		
		$list = array();
		foreach($methods as $id => $arr){
			$list[$id] = array(
					'id' => $id,
					'name' => $arr['name'],
					'active' => ($id==$active),
					'disabled' => (!in_array($id,$match)),
					'text' => (isset($texts[$arr['field']])) ? $texts[$arr['field']] : false,
			);
		}
		
		$list = $this->correct_list($list,$data);		
				
		return $list;
	}
			
}
?>