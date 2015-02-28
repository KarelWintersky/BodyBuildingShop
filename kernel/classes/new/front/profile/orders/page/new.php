<?php
Class Front_Profile_Orders_Page_New{
	
	/*
	 * расширение информации о заказах, сделанных по новой системе
	 * */
	
	private $registry;
						
	public function __construct($registry){
		$this->registry = $registry;
	}	

	private function numbers($order){
		$numbers = array(
				'order' => $order['sum_full'],
				'discount' => $order['sum_full'] - $order['sum_with_discount'],
				'discount_percent' => $order['discount_percent'],
				'delivery' => $order['delivery_costs'],
				'nalog' => $order['nalog_costs'],
				'overall' => $order['overall_sum'],
				);
		
		return $numbers;
	}
	
	public function do_extend($order){
		$payment = Front_Order_Data_Payment::get_methods($order['payment_method_id']);
		$order['payment_name'] = $payment['name'];
				
		$order['print_bill'] = ($order['payment_method_id']==2 || $order['account_extra_payment']==2);

		$order['numbers'] = $this->numbers($order);
		
		return $order;
	}
}
?>