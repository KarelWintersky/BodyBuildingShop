<?php
Class Front_Order_Helper{

	public static function delivery_payment_texts($items){
		$texts = array();
		
		$fields = array();
		foreach($items as $i)
			$fields[] = sprintf("'%s'",$i['field']);
		
		$qLnk = mysql_query(sprintf("
				SELECT
					name,
					value
				FROM
					dp_params
				WHERE
					name  IN (%s)
				",
				implode(",",$fields)
				));
		while($t = mysql_fetch_assoc($qLnk)) $texts[$t['name']] = $t['value'];
		
		return $texts;
	}
	
	public static function done_link($order_num){
		/*
		 * записываем в сессию номер заказа и выдаем ссылку для редиректа на /order/done/
		 * */
		
		$_SESSION['done_order_num'] = $order_num;
		
		return '/order/done/';
		
	}
			
}
?>