<?php
Class Front_Order_Write_Coupon{
	
	/*
	 * "применяем" (аннулируем) купон
	 * */	
	
	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	public function truncate_coupon($hash,$order_num){
		if(!$hash) return false;
		
		mysql_query(sprintf("
				UPDATE
					coupons
				SET
					usedon = NOW(),
					usedby = '".$user_id."',
					order_id = '%s',
					status = 4
				WHERE
					hash = '%s'
				",
				$order_num,
				mysql_real_escape_string($hash)
				));
	}
}
?>