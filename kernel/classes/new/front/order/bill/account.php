<?php
Class Front_Order_Bill_Account{

	private $registry;
				
	public function __construct($registry){
		$this->registry = $registry;
	}	
				
	public function get_data($num,$skip_user_match){
		if(!$this->registry['userdata']) return false;
		
		$qLnk = mysql_query(sprintf("
				SELECT
					account_orders.*,
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
					account_orders
				LEFT OUTER JOIN users ON users.id = account_orders.user_id
				WHERE
					account_orders.id = '%d'
					AND
					users.id = '%d'
				",
				$num[0],
				$this->registry['userdata']['id']
				));
		$order = mysql_fetch_assoc($qLnk);
		if(!$order) return false;

		$output = array(
				'num' => implode('/',$num),
				'name' => $order['user_name'],
				'address' => Common_Address::implode_address($order),
				'price' => Common_Useful::price2read($order['sum']),
		);
		
		return $output;		
	}			
}
?>