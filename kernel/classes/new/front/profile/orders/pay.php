<?php
Class Front_Profile_Orders_Pay Extends Common_Rq{
	
	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}	

	public function check_order($num){
		$arr = explode('-',$num);
		if(count($arr)!=3) return false;
		
		$qLnk = mysql_query(sprintf("
				SELECT
					*
				FROM
					orders
				WHERE
					user_id = '%d'
					AND
					id = '%d'
					AND
					user_num = '%d'
					AND
					payment_method = '%s'
					AND
					by_card = 1
					AND
					status = 1
					AND
					payment_method_id <> '0'
				",
				$this->registry['userdata']['id'],
				$arr[0],
				$arr[1],
				mysql_real_escape_string($arr[2])
				));
		$order = mysql_fetch_assoc($qLnk);
		if(!$order) return false;
	
		$order['num'] = sprintf('%d/%d/%s',
				$order['id'],
				$order['user_num'],
				$order['payment_method']
		);		
		
		$this->set_vars($order);
		
		return true;
	}
	
	private function set_vars($order){
	
		$this->registry['longtitle'] = sprintf('Оплата заказа № %s',$order['num']);
	
                $R = $this->registry['config']['robokassa'];
                
		$unique_id = $order['ai'];
		$desc = sprintf('Оплата заказа № %s в Бодибилдинг-Магазине',$order['num']);
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
	}	
		
}
?>