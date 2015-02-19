<?php
Class Front_Order_Bill_Cart{

	private $registry;
				
	public function __construct($registry){
		$this->registry = $registry;
	}	
				
	public function get_data(){
		$qLnk = mysql_query("
				SELECT
				orders.*,
				users.name AS user_name,
				users.email AS user_email,
				users.zip_code AS zip_code,
				users.region AS region,
				users.city AS city,
				users.street AS street,
				users.house AS house,
				users.corpus AS corpus,
				users.flat AS flat
				FROM
				orders
				LEFT OUTER JOIN users ON users.id = orders.user_id
				WHERE
				orders.id = '".$order_num[0]."'
				AND
				orders.user_num	= '".$order_num[1]."'
				AND
				orders.payment_method = '".$order_num[2]."'
				LIMIT 1;
				");
		if(mysql_num_rows($qLnk)>0){
			$order = mysql_fetch_assoc($qLnk);
			if($_SESSION['user_id']==$order['user_id']){
		
				$order['address'] = $this->registry['logic']->implode_address($order);
				$order['num'] = $num;
		
				$this->registry['logic']->item_rq('bill_show',$order);
		
				return true;
			}
		}		
	}			
}
?>