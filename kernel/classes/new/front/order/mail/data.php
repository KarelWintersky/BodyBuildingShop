<?php
Class Front_Order_Mail_Data{
	
	private $registry;
	
	private $Front_Order_Data_Cart_Gift;
	private $Front_Order_Mail_Goods;
	
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Mail_Goods = new Front_Order_Mail_Goods($this->registry);
		$this->Front_Order_Data_Cart_Gift = new Front_Order_Data_Cart_Gift($this->registry);
	}	
	
	public function get_data($num){
		$num = explode('/',$num);
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
								orders.user_num	= '%d'
								AND
								orders.payment_method = '%s'
							LIMIT 1;
							",
						$num[0],
						$num[1],
						mysql_real_escape_string($num[2])
						));
		$order = mysql_fetch_assoc($qLnk);
		if(!$order) return false;
		
		$order['num'] = implode('/',$num);
		
		$order['address'] = Common_Address::implode_address($order);
		
		$order['tech'] = Front_Order_Helper::get_tech_data($order);
		
		$order['gift'] = $this->Front_Order_Data_Cart_Gift->get_gift($order['gift_barcode']);
		
		$order['goods'] = $this->Front_Order_Mail_Goods->get_goods($order);
		
		return $order;
	}
		
}
?>