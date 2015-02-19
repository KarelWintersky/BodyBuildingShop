<?php
Class Front_Order_Done_Data{

	private $registry;
				
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	private function get_message($order){
		
		//квитанция
		if($order['payment_method_id']==2 && $order['delivery_type']==1) 
			$msg_id = 1;
		
		//WM
		elseif($order['payment_method_id']==3 && $order['delivery_type']==1) 
			$msg_id = 2;
		
		//личный счет
		elseif($order['payment_method_id']==6 && $order['delivery_type']==1) 
			$msg_id = 3;
			
		//наложка
		elseif($order['payment_method_id']==1 && $order['delivery_type']==1) 
			$msg_id = 4;
		
		//оплата курьеру
		elseif($order['payment_method_id']==5 && $order['delivery_type']==2) 
			$msg_id = 5;
		
		//квитанция курьер
		elseif($order['payment_method_id']==2 && $order['delivery_type']==2) 
			$msg_id = 6;
		
		//WM курьер
		elseif($order['payment_method_id']==3 && $order['delivery_type']==2) 
			$msg_id = 7;
		
		//личный счет курьер
		elseif($order['payment_method_id']==6 && $order['delivery_type']==2) 
			$msg_id = 8;
		
		//транспортная компания
		elseif($order['delivery_type']==3) 
			$msg_id = 9;
		
		//самовывоз
		elseif($order['delivery_type']==4) 
			$msg_id = 10;
		
		$qLnk = mysql_query(sprintf("SELECT IFNULL(text,'') FROM order_msgs WHERE id = '%d';",$msg_id));
		$message = mysql_result($qLnk,0);
		
		return $message;
	}
	
	private function get_order($order_num){
		$num = explode('/',$order_num);
		if(count($num)!=3) return false;
		
		$qLnk = mysql_query(sprintf("
				SELECT
					*
				FROM
					orders
				WHERE
					id = '%d'
					AND
					user_num = '%d'
					AND
					payment_method = '%s'					
				",
				$num[0],
				$num[1],
				mysql_real_escape_string($num[2])
				));
		$order = mysql_fetch_assoc($qLnk);
		
		return $order;
	}
	
	public function get_data(){
		$order_num = (isset($_SESSION['done_order_num'])) ? $_SESSION['done_order_num'] : false;
		if(!$order_num) return false;
		
		$order = $this->get_order($order_num);
		if(!$order) return false;
		
		$order['num'] = $order_num;
		$order['message'] = $this->get_message($order);
		
		return $order;
	}			
}
?>