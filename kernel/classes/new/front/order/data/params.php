<?php
Class Front_Order_Data_Params{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;	
	}	

	private function delivery_only_company($goods){
		/*
		 * доставка только транспортной компанией
		 * */
		
		$flag = false;
		
		foreach($goods as $g)
			if($g['delivery_way_id']==1)
				$flag = true;
		
		return $flag;
	}
	
	private function nalog_payment_available($data){
		/*
		 * доступен ли наложенный платеж
		* */		
		
		$flag = true;
		
		if($data['nalog']['sum']>$this->registry['userdata']['max_nalog']) $flag = false;
		if($data['costs']['post']['no_nalog']) $flag = false;
		
		return $flag;
	}
	
	private function account_payment_available($data){
		/*
		 * доступна ли оплата с личного счета
		 * закрываем ее при нулевом личном счете и незарегистрированным
		 * */
		
		if(!$this->registry['userdata'] || !$this->registry['userdata']['my_account']) return false;
	}
	
	public function get_params($data){
		$params = array(
			'delivery_only_company' => $this->delivery_only_company($data['goods']),
			'nalog_payment_available' => $this->nalog_payment_available($data),
			'account_payment_available' => $this->account_payment_available($data) 
			);
	
		return $data + $params;
	}
	
}
?>