<?php
Class Front_Order_Payment_Card_Done Extends Common_Rq{

	private $registry;
				
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	public function do_page(){
		$order = $this->get_order();
		
		$vars = array(
			'order_num' => sprintf('%d/%d/%s',
					$order['id'],
					$order['user_num'],
					$order['payment_method']
					),
			'overall_sum' => Common_Useful::price2read($order['overall_sum'] - $order['from_account']),
			'message' => $this->do_message($order)
		);
		
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
		
	private function do_message($order){
		
		if($order['delivery_type']==1) $type = 1;
		elseif($order['delivery_type']==2) $type = 2;
		elseif($order['delivery_type']==4 && $order['is_spb']) $type = 3;
		elseif($order['delivery_type']==4 && !$order['is_spb']) $type = 4;
		
		$tech = Front_Order_Helper::get_tech_data($order);
		
		$a = array(
				'type' => (isset($type)) ? $type : false,
				'user_name' => $tech['name'],
				'user_address' => $tech['address'],
				'user_email' => $tech['email'],
				'user_phone' => $tech['phone'],
				);
		
		return $this->do_rq('message',NULL);
	}
	
	public function get_order(){
		if(!Front_Order_Payment_Card_Helper::keys_check()) Front_Order_Payment_Card_Helper::goto_error();
		
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
					orders.ai = '%d'
					AND
					orders.status = '3'
					",
				$_POST['InvId']
				));
		$order = mysql_fetch_assoc($qLnk);
		if(!$order) Front_Order_Payment_Card_Helper::goto_error();
		
		$crc  = strtoupper(md5(sprintf("%s:%s:%s:Shp_item=%s",
				$_POST['OutSum'],
				$_POST['InvId'],
				ROBOKASSA_PW,
				$_POST['Shp_item']
				)));
	
		if($crc!=strtoupper($_POST['SignatureValue'])) Front_Order_Payment_Card_Helper::goto_error();
		
		return $order;
	}
		
}
?>