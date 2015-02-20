<?php
Class Front_Order_Post{

	/*
	 * проверка данных в массиве _POST при субмите шагов бронирования на случай если чего не хватает
	 * */
	
	private static function check_struct($fields){
		foreach($fields as $key => $struct)
			if(!isset($_POST[$key]))
			return false;
		
		return true;		
	}
	
	private static function cart_fields(){
		return array(
				'coupon' => true
				);
	}
	
	private static function delivery_fields(){
		return array(
				'delivery' => true
		);		
	}

	private static function payment_fields(){
		return array(
				'payment' => true
		);
	}	
	
	public static function do_check($type){
		if($type==1) $fields = self::cart_fields();
		if($type==2) $fields = self::delivery_fields();
		if($type==3) $fields = self::payment_fields();
		
		if(!self::check_struct($fields)){
			header('Location: /order/');
			exit();
		}
	}		
	
}
?>