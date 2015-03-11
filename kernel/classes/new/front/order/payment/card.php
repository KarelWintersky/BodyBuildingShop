<?php
Class Front_Order_Payment_Card{

	private $registry;
				
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	public function do_prepare(){
		if(!isset($_GET['id'])) Front_Order_Payment_Card_Helper::goto_error();
		
		$order_id = trim($_GET['id']);
		if(!$order_id) Front_Order_Payment_Card_Helper::goto_error();
		
		$num = explode('/',$order_id);
		if(count($num)!=3) Front_Order_Payment_Card_Helper::goto_error();
		
		$qLnk = mysql_query(sprintf("
			SELECT
				*
			FROM
				orders
			WHERE
				id = '%d'
				AND
				user_num	= '%d'
				AND
				payment_method = '%s'
				AND
				by_card = 1
				AND
				status = 1
				",
				$num[0],
				$num[1],
				mysql_real_escape_string($num[2])
				));
		$order = mysql_fetch_assoc($qLnk);
		if(!$order) Front_Order_Payment_Card_Helper::goto_error();
				
       $R = $this->registry['config']['robokassa'];
                
		$unique_id = $order['ai'];
		$desc = sprintf('Оплата заказа № %s в Бодибилдинг-Магазине',$order_id);
		$sum = $order['overall_sum'] - $order['from_account'];
		$code = 1;
			
		$crc  = md5(sprintf("%s:%s:%s:%s:Shp_item=%s",
				$R['login'],
				$sum,
				$unique_id,
				$R['pass'],
				$code
				));
		
		$vars = array(
				'login' => $R['login'],
				'sum' => $sum,
				'unique_id' => $unique_id,
				'desc' => $desc,
				'signature' => $crc,
				'code' => $code,
				'curr' => $R['curr'],
				'lang' => $R['lang'],
				'url' => $R['url'],
		);
		
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
		
		$this->registry->set('longtitle','Оплата заказа');
	}
			
}
?>