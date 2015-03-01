<?php
Class Front_Order_Helper{
	
	public static function done_link($order_num){
		/*
		 * записываем в сессию номер заказа и выдаем ссылку для редиректа на /order/done/
		 * */
		
		$_SESSION['done_order_num'] = $order_num;
		
		return '/order/done/';
		
	}
			
}
?>