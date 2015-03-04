<?php
Class Front_Order_Mail_Card{
	
	/*
	 * уведомления об оплате по карте
	 * вызывается отдельно, а не из Front_Order_Mail
	 * */
	
	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	private function get_data($ai){
		$qLnk = mysql_query(sprintf("
				SELECT
					orders.*,
					users.email AS user_email				
				FROM
					orders
				LEFT OUTER JOIN users ON users.id = orders.user_id
				WHERE
					orders.ai = '%d'
				",
				$ai
				));
		$order = mysql_fetch_assoc($qLnk);
		
		return $order;
	}
	
	private function to_admins($order){
		$emails = explode('::',ADMINS_EMAILS);

		$replace = array(
				'ORDER_NUM_SUBJ' => sprintf('Заказ %s оплачен по банковской карте',$order['num']),
				'ORDER_NUM_TEXT' => sprintf('Заказ %s оплачен по банковской карте на сумму %s руб.',
						$order['num'],
						$order['overall_price'] - $order['from_account']
						)
				);
		
		foreach($emails as $admin_mail)
			$mailer = new Mailer($this->registry,32,$replace,$admin_mail,false,'windows-1251');
	}

	private function to_user($order){
		$replace = array(
				'ORDER_NUM' => $order['num'],
				'OVERALL_PRICE' => $order['overall_sum'],
				'DELIVERY_COMMENT' => ($order['delivery_type']==1) 
					? 'В ближайшее время заказ будет передан в обработку. После отправки Вы получите уведомление где будет указана точная дата отправки и номер отправления для отслеживания посылки на сайте почты России.' 
					: 'Если заказ Был сделан до 12 часов то курьер свяжется с Вами в течении дня. В противном случае - на следующий рабочий день после заказа. (За исключением случаев форс-мажора или невозможности связаться с Вами по указанному Вами телефону).',
		);
		
		$mailer = new Mailer($this->registry,35,$replace,$order['user_email']);	
	}	
	
	public function send_letter($ai){
		$order = $this->get_data($ai);
		if(!$order) return false;
		
		$order['num'] = sprintf('%d/%d/%s',
				$order['id'],
				$order['user_num'],
				$order['payment_method']
				);

		$this->to_admins($order);
		$this->to_user($order);		
	}	
	
}
?>