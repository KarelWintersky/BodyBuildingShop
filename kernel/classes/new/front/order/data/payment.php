<?php
Class Front_Order_Data_Payment{

	public static function get_methods($method_id = false){
		$methods = array(
				1 => array(
					'name' => 'Заказать наложенным платежом',
				),
				2 => array(
					'name' => 'Получить счет на предоплату через банк',
				),
				3 => array(
					'name' => 'Оплата через WebMoney, Яндекс-деньги',
				),
				4 => array(
					'name' => 'Оплата банковской картой',
				),
				5 => array(
					'name' => 'Наличными курьеру или в магазине',
				),
				6 => array(
					'name' => 'Оплата c лицевого счета в нашем магазине',
				),
				7 => array(
					'name' => 'Другие платежные системы',
				)				
			);

		return (!$method_id)
			? $methods
			: $methods[$method_id];
	}
	
}
?>