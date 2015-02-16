<?php
Class Front_Order_Mail_Bill{
	
	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	public function send_letter(){
			$order_num = explode('/',$num);
		
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
				$order['address'] = $this->implode_address($order);
				$order['num'] = $num;
		
				ob_start();
				$this->item_rq('bill',$order);
				$bill = ob_get_contents();
				ob_end_clean();
		
				$pdfmanager = new Pdfmanager($this->registry);
				$attach_string = $pdfmanager->fileCompose($bill);
		
				$replace_arr = array(
						'ORDER_NUM' => $num
				);
		
				$mailer = new Mailer($this->registry,13,$replace_arr,$order['user_email'],$attach_string);
		
			}
		
	}
}
?>