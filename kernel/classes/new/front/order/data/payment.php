<?php
Class Front_Order_Data_Payment{

	public static function get_methods($method_id = false){
		$methods = array(
				1 => array(
					'name' => 'Заказать наложенным платежом',
					'short_name' => 'Наложка',
					'class_alias' => 'nalog'
				),
				2 => array(
					'name' => 'Оплата через коммерческий банк или Сбербанк',
					'short_name' => 'Квитанция',
				),
				3 => array(
					'name' => 'Оплата через WebMoney, Яндекс-деньги',
					'short_name' => 'WebMoney, Яндекс-деньги',
				),
				4 => array(
					'name' => 'Оплата банковской картой, QIWI, RBK Money и другие системы',
					'short_name' => 'Robokassa',
				),
				5 => array(
					'name' => 'Наличными курьеру или в магазине',
					'short_name' => 'Наличными',
					'class_alias' => 'courier'
				),
				6 => array(
					'name' => 'Оплата c лицевого счета в нашем магазине',
					'short_name' => 'Лицевой счет',
					'class_alias' => 'account'
				),
			);

		return (!$method_id)
			? $methods
			: $methods[$method_id];
	}
	
}
?>