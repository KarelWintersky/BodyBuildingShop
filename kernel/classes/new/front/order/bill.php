<?php
Class Front_Order_Bill Extends Common_Rq{

	private $registry;
				
	public function __construct($registry){
		$this->registry = $registry;
	}	
				
		private function print_bill($num){		
			$order_num = explode('/',$num);

			if(count($order_num)==3){
				if($order_num[2]=='A' || $order_num[2]=='А'){
					
					$qLnk = mysql_query("
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
											account_orders.id = '".$order_num[0]."'
										LIMIT 1;
										");
					if(mysql_num_rows($qLnk)>0){
						$order = mysql_fetch_assoc($qLnk);
						if($_SESSION['user_id']==$order['user_id']){
							
							$order['address'] = $this->registry['logic']->implode_address($order);
							$order['num'] = $num;
							$order['overall_price'] = $order['sum'];
							$order['from_account'] = 0;
							
							$this->registry['logic']->item_rq('bill_show',$order);	
							
							return true;
						}
					}
					return true;
				}else{
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

			header('Location: /');
			
		}
			
}
?>