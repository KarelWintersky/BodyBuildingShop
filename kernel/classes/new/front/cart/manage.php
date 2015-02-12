<?php
Class Front_Cart_Manage{

	/*
	 * функции работы с корзиной
	 * добавление
	 * удаление
	 * изменения количества
	 * */
	
	private $registry;
	
	private $Front_Order_Data;
	private $Front_Cart_Write;
	private $Front_Order_Cart_Values;
	private $Front_Order_Cart_Gift;
	
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Data = new Front_Order_Data($this->registry);
		$this->Front_Cart_Write = new Front_Cart_Write($this->registry);
		$this->Front_Order_Cart_Values = new Front_Order_Cart_Values($this->registry);
		$this->Front_Order_Cart_Gift = new Front_Order_Cart_Gift($this->registry);
	}	
		
	public function restruct($goods){
		$cart = $this->registry['CL_cart_string']->get_cart_from_string();
		
		foreach($goods as $key => $amount)
			if(isset($cart[$key])) $cart[$key]['amount'] = $amount;
			else unset($cart[$key]);
				
		foreach($cart as $key => $arr)
			if(!$arr['amount'])
				unset($cart[$key]);
		
		$this->Front_Cart_Write->write_from_array($cart);
		
		if(!count($cart)){
			$resp = array('empty' => true);
		}else{
			$data = $this->Front_Order_Data->get_data($cart);
			
			$resp = array(
					'values' => $this->Front_Order_Cart_Values->do_block($data),
					'gift' => $this->Front_Order_Cart_Gift->do_block($data),
					'empty' => false
			);			
		}
				
		echo json_encode($resp);
	}
	
}
?>