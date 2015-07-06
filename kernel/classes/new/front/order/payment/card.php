<?php
Class Front_Order_Payment_Card{

	private $registry;
				
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	public function do_prepare(){
		if(!isset($_GET['id'])) Front_Order_Payment_Card_Helper::goto_error();
		
		$order_id = trim($_GET['id']);
		if(!$order_id) Front_Order_Payment_Card_Helper::goto_error();

		$num = explode('/',$order_id);
		if(count($num)!=3) Front_Order_Payment_Card_Helper::goto_error();

		$qLnk = mysql_query(sprintf("
				SELECT
					*
				FROM
					orders
				WHERE
					id = '%d'
					AND
					user_num	= '%d'
					AND
					payment_method = '%s'
					AND
					by_card = 1
					AND
					status = 1
				",
				$num[0],
				$num[1],
				mysql_real_escape_string($num[2])
		));
		$order = mysql_fetch_assoc($qLnk);
		if(!$order) Front_Order_Payment_Card_Helper::goto_error();

		$Y = $this->registry['config']['yandex_money'];
		
		$vars = array(
				'account_number' => $Y['account_number'],
				'comment' => sprintf('Бодибилдинг Магазин. Оплата заказа %s',$order_id),
				'ai' => $order['ai'],
				'sum' => $order['overall_sum'] - $order['from_account']
				);
		
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
		
		$this->registry->set('longtitle','Оплата заказа');		
	}
	
}
?>