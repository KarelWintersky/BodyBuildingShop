<?php
Class Front_Order_Data_Payment{

	public static function get_methods($method_id = false){
		$methods = array(
				1 => array(
					'name' => 'Заказать наложенным платежом',
					'short_name' => 'Наложка',
					'tech_name' => 'наложенный платеж',
					'class_alias' => 'nalog'
				),
				2 => array(
					'name' => 'Оплата через коммерческий банк или Сбербанк',
					'short_name' => 'Квитанция',
					'tech_name' => 'в банке по квитанции',
				),
				3 => array(
					'name' => 'Оплата через WebMoney, Яндекс-деньги',
					'short_name' => 'WebMoney, Яндекс-деньги',
					'tech_name' => 'электронные деньги',
				),
				4 => array(
					'name' => 'Оплата банковской картой, QIWI, RBK Money и другие системы',
					'short_name' => 'Robokassa',
					'tech_name' => 'по банковской карте',
                    'dont_display_on_frontend' => true
				),
				5 => array(
					'name' => 'Наличными курьеру или в магазине',
					'short_name' => 'Наличными',
					'class_alias' => 'courier',
					'tech_name' => 'наличными курьеру',
				),
				6 => array(
					'name' => 'Оплата c лицевого счета в нашем магазине',
					'short_name' => 'Лицевой счет',
					'class_alias' => 'account',
					'tech_name' => 'полностью со счета',
				),
				7 => array(
						'name' => 'Оплата банковской картой',
						'short_name' => 'Банковской картой',
						'tech_name' => 'по банковской карте',
				),				
			);

		return (!$method_id)
			? $methods
			: $methods[$method_id];
	}
	
}
?>