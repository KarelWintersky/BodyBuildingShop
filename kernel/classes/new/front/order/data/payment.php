<?php
Class Front_Order_Data_Payment{

	public static function get_methods($method_id = false){
		$methods = array(
				1 => array(
					'name' => 'Заказать наложенным платежом',
					'short_name' => 'Наложка',
					'field' => 'pay_nalog'
				),
				2 => array(
					'name' => 'Получить счет на предоплату через банк',
					'short_name' => 'Квитанция',
					'field' => 'pay_bill'
				),
				3 => array(
					'name' => 'Оплата через WebMoney, Яндекс-деньги',
					'short_name' => 'WebMoney, Яндекс-деньги',
					'field' => 'pay_webmoney'
				),
				4 => array(
					'name' => 'Оплата банковской картой или другие платежные системы',
					'short_name' => 'Robokassa',
					'field' => 'pay_card'
				),
				5 => array(
					'name' => 'Наличными курьеру или в магазине',
					'short_name' => 'Наличными',
					'field' => 'pay_nal'
				),
				6 => array(
					'name' => 'Оплата c лицевого счета в нашем магазине',
					'short_name' => 'Лицевой счет',
					'field' => 'pay_account'
				),
				/*7 => array(
					'name' => 'Другие платежные системы',
					'field' => 'pay_other'
				)*/				
			);

		return (!$method_id)
			? $methods
			: $methods[$method_id];
	}
	
}
?>