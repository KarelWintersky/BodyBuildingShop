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
	
	public static function keys_check(){
		$keys = array(
				'notification_type',
				'withdraw_amount',
				'sha1_hash',
				'label',
				'operation_id',
				'amount',
				'currency',
				'datetime',
				'sender',
				'codepro',
				'notification_secret',
		);
	
		foreach($keys as $k)
			if(!isset($_POST[$k]))
			return false;
	
		return true;
	}	
}
?>