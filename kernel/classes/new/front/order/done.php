<?php
Class Front_Order_Done{

	private $registry;
	
	private $Front_Order_Done_Data;
				
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Done_Data = new Front_Order_Done_Data($this->registry);
	}	
		
	public function do_vars(){
		$data = $this->Front_Order_Done_Data->get_data();
	}
	
	private function cart_done_msg(){
		
		/*$goods_arr = array();
		foreach($order_vals['goods_data'] as $arr){
			$goods_arr[$arr['goods_id']] = $arr['full_name'];
		}
		
		$user_address = $this->registry['logic']->implode_address($this->registry['userdata']);*/
	
		$replace_arr = array(
				'ORDER_NUM' => $this->registry['order_id'],
				'OVERALL_SUM' => intval($order_vals['overall_price']-$this->registry['from_account']),
				'USER_NAME' => $this->registry['userdata']['name'],
				'USER_ADDRESS' => $user_address,
				'USER_MAIL' => $this->registry['userdata']['email'],
				'USER_PHONE' => $order_vals['phone'],
				'FREE_DELIVERY_SUM' => FREE_DELIVERY_SUM,
				'ADDITIONAL' => $additional
		);
	
		foreach($replace_arr as $find => $replace)
			$cart_done_msg = str_replace('{'.$find.'}', $replace, $cart_done_msg);
		
		if($msg_id==1 || $msg_id==6){
			$this->registry['do_bill'] = true;
		}
	}			
}
?>