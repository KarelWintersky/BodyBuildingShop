<?php
Class Front_Order_Done_Data{

	private $registry;
				
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	private function get_order($order_num){
		$num = explode('/',$order_num);
		if(count($num)!=3) return false;
		
		$qLnk = mysql_query(sprintf("
				SELECT
					orders.*,
					users.login AS user_login,
					users.name AS user_name,
					users.phone AS user_phone,
					users.email AS user_email,
					users.zip_code AS zip_code,
					users.region AS region,
					users.city AS city,
					users.street AS street,
					users.house AS house,
					users.corpus AS corpus,
					users.flat AS flat,
					users.personal_discount AS personal_discount				
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
		
		return $order;
	}
	
	public function get_data(){
		$order_num = (isset($_SESSION['done_order_num'])) ? $_SESSION['done_order_num'] : false;
		if(!$order_num) return false;
		
		$order = $this->get_order($order_num);
		if(!$order) return false;
		
		$order['num'] = $order_num;
		
		return $order;
	}			
}
?>