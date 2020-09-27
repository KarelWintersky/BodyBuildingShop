<?php
Class Front_Order_Payment_Card_Helper{
		
	public static function goto_error(){
		header('Location: /order/card/error/');
		exit();
	}
	
	public static function goto_index(){
		header('Location: /');
		exit();		
	}
	
}
?>