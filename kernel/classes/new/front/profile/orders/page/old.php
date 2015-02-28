<?php
Class Front_Profile_Orders_Page_Old{
	
	/*
	 * расширение информации о заказах, сделанных по старой системе
	 * */
	
	private $registry;
						
	public function __construct($registry){
		$this->registry = $registry;
	}	

	private function payment_name($order){
		if($order['payment_method']=='Н' || $order['payment_method']=='H')
			return 'наложенным платежом';
		elseif($order['payment_method']=='W')
			return 'электронными деньгами';
		elseif($order['payment_method']=='П'){
			if($order['by_card'])
				return 'банковской картой или через платежные системы';
			elseif($order['pay2courier'])
				return 'курьеру';
			elseif($order['from_account'])
				return 'с личного счета';
			else
				return 'предоплата';
		}		
	}
	
	private function numbers($order){
		$discount_sum = ceil($order['discount']*$order['sum']/100);
		$after_discount = $order['sum'] - $discount_sum;
		
		$nalog_costs = ($order['payment_method']=='Н' || $order['payment_method']=='H')
			? $after_discount*PREPAY_DISCOUNT/100 
			: 0;
		
		$numbers = array(
				'order' => $order['sum'],
				'discount' => $discount_sum,
				'discount_percent' => $order['discount'],
				'delivery' => $order['delivery_costs'],
				'nalog' => $nalog_costs,
				'overall' => $after_discount + $order['delivery_costs'] + $nalog_costs,
		);
	
		return $numbers;
	}	
	
	public function do_extend($order){
		$order['payment_name'] = $this->payment_name($order);
		
		$order['print_bill'] = ($order['status']==1 && $order['payment_method']!='H' && $order['payment_method']!='Н');
		
		$order['numbers'] = $this->numbers($order);
				
		return $order;
	}
}
?>