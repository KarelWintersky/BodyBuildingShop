<?php
Class Front_Order_Data_Delivery{

	public static function get_methods($method_id = false){
		$methods = array(
				1 => array(
					'name' => 'Доставка по почте',
					'payment' => array(1,2,3,4,6,7)
				),
				2 => array(
					'name' => 'Доставка курьером',
					'payment' => array(2,3,4,5,6,7)
				),
				/*3 => array(
				 'name' => 'Доставка транспортной компанией',
						'payment' => array(2,3,4,6,7)
				),*/
				4 => array(
					'name' => 'Самовывоз',
					'payment' => array(2,3,4,5,6,7)
				),
		);	

		return (!$method_id)
			? $methods
			: $methods[$method_id];
	}
	
}
?>