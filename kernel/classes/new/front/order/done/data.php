<?php
Class Front_Order_Done_Data{

	private $registry;
				
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	private function get_message(){
		if($order_vals['payment_method']==2 && $order_vals['delivery_type']==1){ //квитанция
			$msg_id = 1;
		}elseif($order_vals['payment_method']==3 && $order_vals['delivery_type']==1){ //WM
			$msg_id = 2;
		}elseif($order_vals['payment_method']==6 && $order_vals['delivery_type']==1){ //личный счет
			$msg_id = 3;
			$replace_arr['OVERALL_SUM'] = $this->registry['from_account'];
		}elseif($order_vals['payment_method']==1 && $order_vals['delivery_type']==1){ //наложка
			$msg_id = 4;
		}elseif($order_vals['payment_method']==5 && $order_vals['delivery_type']==2){ //оплата курьеру
			$msg_id = 5;
		}elseif($order_vals['payment_method']==2 && $order_vals['delivery_type']==2){ //квитанция курьер
			$msg_id = 6;
		}elseif($order_vals['payment_method']==3 && $order_vals['delivery_type']==2){ //WM курьер
			$msg_id = 7;
		}elseif($order_vals['payment_method']==6 && $order_vals['delivery_type']==2){ //личный счет курьер
			$msg_id = 8;
			$replace_arr['OVERALL_SUM'] = $this->registry['from_account'];
		}elseif($order_vals['delivery_type']==3){ //транспортная компания
			$msg_id = 9;
		}elseif($order_vals['delivery_type']==4){ //самовывоз
			$msg_id = 10;
		}
		
		$qLnk = mysql_query("SELECT order_msgs.text FROM order_msgs WHERE order_msgs.id = '".$msg_id."';");
		$cart_done_msg = (mysql_num_rows($qLnk)>0) ? mysql_result($qLnk,0) : '';		
	}
	
	public function get_data(){
		$order_num = (isset($_SESSION['done_order_num'])) ? $_SESSION['done_order_num'] : false;
		if(!$order_num) return false;
		
		var_dump($order_num);
		exit();
	}			
}
?>