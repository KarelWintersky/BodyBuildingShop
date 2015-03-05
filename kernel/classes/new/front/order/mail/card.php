<?php
Class Front_Order_Mail_Card Extends Common_Rq{
	
	/*
	 * уведомления об оплате по карте
	 * вызывается отдельно, а не из Front_Order_Mail
	 * */
	
	private $registry;
	
	private $Front_Order_Done_Message;
	
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Done_Message = new Front_Order_Done_Message($this->registry);
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
						$order['overall_sum'] - $order['from_account']
						)
				);
		
		foreach($emails as $admin_mail)
			$mailer = new Mailer($this->registry,32,$replace,$admin_mail,false,'windows-1251');
	}

	private function to_user($order){
		
		$a = array(
				'num' => $order['num'],
				'order_sum' => Common_Useful::price2read($order['overall_sum'] - $order['from_account']),
				'message' => $this->Front_Order_Done_Message->do_message($order),
				);
		
		$html = $this->do_rq('tpl',$a);
		
		$this->registry['CL_mail']->send_mail(
				$order['tech']['email'],
				sprintf('Ваш заказ %s успешно оплачен',$order['num']),
				$html
		);		
			
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