<?php
Class Front_Order_Mail_Notify_Html Extends Common_Rq{
	
	private $registry;
	
	private $Front_Order_Mail_Notify_Table;
	
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Mail_Notify_Table = new Front_Order_Mail_Notify_Table($this->registry);
	}	
			
	private function upper_text($order){
		if($order['delivery_type']==1) return $this->do_rq('deliverypost',$order);
		
		return false;
	}
	
	private function lower_text($order){
		$html = array();
		
		if($order['payment_method_id']==2) $html[] = $this->do_rq('paymentbill',$order);
		elseif($order['payment_method_id']==3) $html[] = $this->do_rq('paymentwm',$order);
		elseif($order['payment_method_id']==6) $html[] = $this->do_rq('paymentaccount',$order);
		
		if($order['delivery_type']==4) $html[] = $this->do_rq('deliveryself',$order);
		elseif($order['delivery_type']==2) $html[] = $this->do_rq('deliverycourier',$order);
		
		return implode('',$html);
	}
	
	public function print_html($order){
		
		$delivery = Front_Order_Data_Delivery::get_methods($order['delivery_type']);
		$payment = Front_Order_Data_Payment::get_methods($order['payment_method_id']);
		
		$a = array(
				'num' => $order['num'],
				'date' => date('d.m.Y H:i',strtotime($order['made_on'])),
				'user_name' => $order['tech']['name'],
				'user_email' => $order['tech']['email'],
				'table' => $this->Front_Order_Mail_Notify_Table->print_goods($order),
				'wishes' => $order['wishes'],
				'delivery_name' => $delivery['name'],
				'payment_name' => ($payment) ? $payment['name'] : false,
				'upper_text' => $this->upper_text($order),
				'lower_text' => $this->lower_text($order),
				);
		
		return $this->do_rq('tpl',$a);
	}	
	
}
?>