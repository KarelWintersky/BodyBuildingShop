<?php
Class Front_Order_Done_Message Extends Common_Rq{

	private $registry;
					
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	private function get_type($order){
		/*почта + наложка*/
		if(
				$order['delivery_type']==1 
				&& 
				($order['payment_method_id']==1 || $order['account_extra_payment']==1)
				)
			$type = 1;
		
		/*почта + (предоплата через банк ИЛИ яндекс.деньги)*/
		elseif(
				$order['delivery_type']==1 
				&& 
				($order['payment_method_id']==2 || $order['payment_method_id']==3 || $order['account_extra_payment']==2 || $order['account_extra_payment']==3)
				)
			$type = 2;		
		
		/*почта + (банковская карта ИЛИ личный счет полный)*/
		elseif(
				$order['delivery_type']==1
				&&
				($order['payment_method_id']==4 || $order['account_extra_payment']==4 || ($order['payment_method_id']==6 && !$order['account_extra_payment']))
				)
			$type = 3;		
		
		/*курьер + наличные*/
		elseif(
				$order['delivery_type']==2
				&&
				($order['payment_method_id']==5 || $order['account_extra_payment']==5)
			)
			$type = 4;		
		
		/*курьер + (предоплата через банк ИЛИ яндекс.деньги)*/
		elseif(
				$order['delivery_type']==2
				&&
				($order['payment_method_id']==2 || $order['payment_method_id']==3 || $order['account_extra_payment']==2 || $order['account_extra_payment']==3)
			)
			$type = 5;		
		
		/*курьер + (банковская карта ИЛИ личный счет полный) + СПБ*/
		elseif(
				$order['delivery_type']==2
				&&
				($order['payment_method_id']==4 || $order['account_extra_payment']==4 || ($order['payment_method_id']==6 && !$order['account_extra_payment']))
				&&
				$order['is_spb']
			)
			$type = 6;		
		
		/*курьер + (банковская карта ИЛИ личный счет полный) + НЕ СПБ*/
		elseif(
				$order['delivery_type']==2
				&&
				($order['payment_method_id']==4 || $order['account_extra_payment']==4 || ($order['payment_method_id']==6 && !$order['account_extra_payment']))
				&&
				!$order['is_spb']
			)
			$type = 7;		
		
		/*самовывоз + СПБ*/
		elseif(
				$order['delivery_type']==4
				&&
				$order['is_spb']
			)
			$type = 8;
				
		/*самовывоз + НЕ СПБ*/
		elseif(
				$order['delivery_type']==4
				&&
				!$order['is_spb']
			)
			$type = 9;

		$type = (isset($type)) ? $type : false;
		
		return $type;
	}
	
	public function do_message($order){
		$tech = Front_Order_Helper::get_tech_data($order);
		
		$a = array(
				'type' => $this->get_type($order),
				'num' => $order['num'],
				'user_name' => $tech['name'],
				'user_address' => $tech['address'],
				'user_email' => $tech['email'],
				'user_phone' => $tech['phone'],
				'order_sum' => Common_Useful::price2read($order['overall_sum'])
				);
		
		return $this->do_rq('text',$a);
	}
				
}
?>