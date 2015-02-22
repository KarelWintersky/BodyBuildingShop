<?php
Class Adm_Orders_Content{

	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}
		
	public function order_check($num){
		$num = explode('-',$num);
		if(count($num)!=3) return false;
	
		$qLnk = mysql_query(sprintf("
				SELECT
					orders.*,
					users.name AS user_name,
					users.zip_code AS zip_code,
					users.region AS region,
					users.district AS district,
					users.city AS city,
					users.street AS street,
					users.house AS house,
					users.corpus AS corpus,
					users.flat AS flat
				FROM
					orders
				LEFT OUTER JOIN users ON users.id = orders.user_id
				WHERE
					orders.id = '%d'
					AND
					orders.user_num = '%d'
					AND
					orders.payment_method = '%s'
				",
				$num[0],
				$num[1],
				mysql_real_escape_string($num[2])
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
		$vars = array(
			'num' => $order['num'],
			'delivery_name' => $this->delivery_name($order) 
		);
		
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);		
	}
	
	private function delivery_name($order){
		
	}
	
}
?>