<?php
Class Front_Order_Delivery_Methods{

	/*
	 * тут формируется список способов доставки 
	 * в зависимости от уровня авторизации покупателя
	 * */
	
	private $registry;
					
	public function __construct($registry){
		$this->registry = $registry;
	}		
	
	private function correct_list($list){
		if($this->registry['userdata']) return $list;
		
		//закрываем доставку по почте для незарегистрированных
		$list[1]['disabled'] = true; $list[1]['active'] = false;
		
		if(!$list[2]['active'] && !$list[4]['active']) $list[2]['active'] = true;
		
		return $list;
	}
	
	public function get_actual_list(){
		$active = $this->registry['CL_storage']->get_storage('delivery');
			$active = ($active) ? $active : 1;
		
		$methods = Front_Order_Data_Delivery::get_methods();

		$texts = Front_Order_Helper::delivery_payment_texts($methods);
		
		$list = array();
		foreach($methods as $id => $arr){
			$list[$id] = array(
					'id' => $id,
					'name' => $arr['name'],
					'active' => ($id==$active),
					'disabled' => false,
					'text' => (isset($texts[$arr['field']])) ? $texts[$arr['field']] : false,
					'class_alias' => $arr['class_alias']
					); 
		}
		
		$list = $this->correct_list($list);
		
		return $list;
	}

			
}
?>