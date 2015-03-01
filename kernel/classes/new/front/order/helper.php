<?php
Class Front_Order_Helper{
	
	public static function done_link($order_num){
		/*
		 * записываем в сессию номер заказа и выдаем ссылку для редиректа на /order/done/
		 * */
		
		$_SESSION['done_order_num'] = $order_num;
		
		return '/order/done/';
		
	}

	public static function get_tech_data($order){
		/*получаем данные о пользователе в зависимости от его регистрации и типа доставки, если незарегистрирован*/
	
		if($order['delivery_type']==1){
			$data = array(
					'name' => $order['user_name'],
					'email' => $order['user_email'],
					'phone' => $order['user_phone'],
					'address' => Common_Address::implode_address($order),
			);
		}elseif($order['delivery_type']==2){
			$arr = explode('::',$order['courier_data']);
				
			$data = array(
					'name' => $arr[0],
					'email' => ($arr[6]) ? $arr[6] : $order['user_email'],
					'phone' => $arr[1],
					'address' => Common_Address::from_courier($order['courier_data']),
			);
		}elseif($order['delivery_type']==4){
			$arr = explode('::',$order['self_data']);
				
			$data = array(
					'name' => $arr[0],
					'email' => $order['user_email'],
					'phone' => $arr[1],
					'address' => Common_Address::implode_address($order),
			);
		}
	
		return $data;
	}	
}
?>