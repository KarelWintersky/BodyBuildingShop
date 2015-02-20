<?php
Class Front_Order_Bill_Cart{

	private $registry;
				
	public function __construct($registry){
		$this->registry = $registry;
	}	
				
	private function auth_check($order,$skip_user_match){
		/*
		 * проверяем, что пользователь запрашивает квитанцию, выставленную именно на него
		 * чтобы никто не мог смотреть чужие квитанции
		 * правда, только для зарегистринованных, ибо для незарегистрированных мы не можем проверить
		 * */
		if($skip_user_match) return true;
		
		return ($order['user_id'])
			? ($this->registry['userdata'] && $this->registry['userdata']['id']==$order['user_id'])
			: !($this->registry['userdata']);
	}
	
	private function make_address($order){
		if($order['delivery_type']==1 && $order['user_id']) return Common_Address::implode_address($order); 
		elseif($order['delivery_type']==2) return Common_Address::from_courier($order['courier_data']);
		else return false;
	}
	
	private function get_name($order){
		if($order['delivery_type']==1) return $order['user_name'];
		elseif($order['delivery_type']==2){
			$arr = explode('::',$order['courier_data']);
			return $arr[0];
		}elseif($order['delivery_type']==4){
			$arr = explode('::',$order['self_data']);
			return $arr[0];			
		}
	}
	
	public function get_data($num,$skip_user_match){
		$qLnk = mysql_query(sprintf("
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
					orders.id = '%d'
					AND
					orders.user_num	= '%d'
					AND
					orders.payment_method = '%s'
				",
				$num[0],
				$num[1],
				mysql_real_escape_string($num[2])
				));
		$order = mysql_fetch_assoc($qLnk);
		if(!$order || !$this->auth_check($order,$skip_user_match)) return false;
				
		$output = array(
				'num' => implode('/',$num),
				'name' => $this->get_name($order),
				'address' => $this->make_address($order),
				'price' => Common_Useful::price2read($order['overall_price'] - $order['from_account']),
				);
		
		return $output;
	}			
}
?>