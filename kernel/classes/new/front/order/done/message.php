<?php
Class Front_Order_Done_Message Extends Common_Rq{

	private $registry;
	
	private $Front_Order_Done_Blocks;
					
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Done_Blocks = new Front_Order_Done_Blocks($this->registry);
	}	
			
	private function get_type($order){

		/*почта + наложка*/
		if(
				$order['delivery_type']==1 
				&& 
				($order['payment_method_id']==1 || $order['account_extra_payment']==1)
				)
			$type = 1;
		
		/*почта + предоплата через банк*/
		elseif(
				$order['delivery_type']==1 
				&& 
				($order['payment_method_id']==2 || $order['account_extra_payment']==2)
				)
			$type = 2;		

		/*почта + яндекс.деньги*/
		elseif(
				$order['delivery_type']==1
				&&
				($order['payment_method_id']==3 || $order['account_extra_payment']==3)
		)
		$type = 25;		
		
		/*почта + банковская карта*/
		elseif(
				$order['delivery_type']==1
				&&
				($order['payment_method_id']==7 || $order['account_extra_payment']==7)
				)
			$type = 3;		
		
		/*почта + личный счет полный*/
		elseif(
				$order['delivery_type']==1
				&&
				($order['payment_method_id']==6 && !$order['account_extra_payment'])
		)
		$type = 35;		
		
		
		/*курьер + наличные*/
		elseif(
				$order['delivery_type']==2
				&&
				($order['payment_method_id']==5 || $order['account_extra_payment']==5)
			)
			$type = 4;		
		
		
		/*курьер + предоплата через банк*/
		elseif(
				$order['delivery_type']==2
				&&
				($order['payment_method_id']==2 || $order['account_extra_payment']==2)
			)
			$type = 5;		

		/*курьер + яндекс.деньги*/
		elseif(
				$order['delivery_type']==2
				&&
				($order['payment_method_id']==3 || $order['account_extra_payment']==3)
		)
		$type = 55;		
		
		
		/*курьер + банковская карта*/
		elseif(
				$order['delivery_type']==2
				&&
				($order['payment_method_id']==7 || $order['account_extra_payment']==7)
			)
			$type = 6;		
		
		
		
		/*курьер + личный счет полный*/
		elseif(
				$order['delivery_type']==2
				&&
				($order['payment_method_id']==6 && !$order['account_extra_payment'])
		)
		$type = 65;		
		
		
	
		
		/*самовывоз + предоплата через банк*/
		elseif(
				$order['delivery_type']==4
				&&
				($order['payment_method_id']==2 || $order['account_extra_payment']==2)				
			)
			$type = 8;

		/*самовывоз + яндекс.деньги*/
		elseif(
				$order['delivery_type']==4
				&&
				($order['payment_method_id']==3 || $order['account_extra_payment']==3)
		)
		$type = 9;
		
		
		/*самовывоз + банковская карта*/
		elseif(
				$order['delivery_type']==4
				&&
				($order['payment_method_id']==7 || $order['account_extra_payment']==7)
		)
		$type = 10;
		
		
		
		/*самовывоз + личный счет полный*/
		elseif(
				$order['delivery_type']==4
				&&
				($order['payment_method_id']==6 && !$order['account_extra_payment'])
		)
		$type = 11;
		
		/*самовывоз + наличными*/
		elseif(
				$order['delivery_type']==4
				&&
				($order['payment_method_id']==5 || $order['account_extra_payment']==5)
		)
		$type = 12;		
		

		
		$type = (isset($type)) ? $type : false;
		
		return $type;
	}
	
	private function data_extend($order){
		$tech = Front_Order_Helper::get_tech_data($order);
				
		return array_merge($order,array(
				'user_name' => $tech['name'],
				'user_address' => $tech['address'],
				'user_email' => $tech['email'],
				'user_phone' => $tech['phone'],		
				'order_sum' => Common_Useful::price2read($order['overall_sum'] - $order['from_account']),
				));
	}
	
	public function do_message($order){
		
		$order = $this->data_extend($order);
		
		$a = array(
				'type' => $this->get_type($order),
				'num' => $order['num'],
				'order_sum' => $order['order_sum'],
				'user_email' => $order['user_email'],
				'user_phone' => $order['user_phone'],
				'from_account' => Common_Useful::price2read($order['from_account']),
				'blocks' => $this->Front_Order_Done_Blocks->get_blocks($order)
				);
		
		return $this->do_rq('text',$a);
	}
				
}
?>