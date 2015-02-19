<?php
Class Front_Order_Done Extends Common_Rq{

	private $registry;
	
	private $Front_Order_Done_Data;
				
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Done_Data = new Front_Order_Done_Data($this->registry);
	}	
		
	public function do_vars(){
		$order = $this->Front_Order_Done_Data->get_data();
		
		$vars = array(
				'message' => $this->do_message($order),
				'bill' => $this->do_bill($order),
				'social' => $this->do_rq('social',NULL)
			);
	
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
	
	private function do_bill($order){
		if($order['payment_method_id']!=2) return false;
		
		return sprintf('<input type="hidden" id="openbill" value="%s">',$order['num']);
	}
	
	private function do_message($order){
		$message = $order['message'];
		
		$replace = array(
			'ORDER_NUM' => $order['num'],
			'OVERALL_SUM' => $order['overall_price']-$order['from_account'],
			'USER_NAME' => '??',
			'USER_ADDRESS' => '??',
			'USER_MAIL' => '??',
			'USER_PHONE' => '??',
			'FREE_DELIVERY_SUM' => FREE_DELIVERY_SUM,
			'ADDITIONAL' => '',
		);
		
		foreach($replace as $f => $r)
			$message = str_replace(sprintf('{%s}',$f), $r, $message);

		return $message;
	}
				
}
?>