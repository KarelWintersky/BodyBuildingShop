<?php
Class Front_Order_Payment_Card_Result{

	private $registry;
	
	private $Front_Order_Mail_Card;
				
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Mail_Card = new Front_Order_Mail_Card($this->registry);
	}	
	
	private function check_sum($ai,$withdraw_amount){
		$qLnk = mysql_query(sprintf("
				SELECT
					overall_sum - from_account AS sum
				FROM
					orders
				WHERE
					ai = '%d'
				",
				$ai
				));
		$order = mysql_fetch_assoc($qLnk);
		if(!$order) return false;
		
		if(!$order['sum']) return false;
		
		return ($order['sum']==$withdraw_amount);
	}
	
	private function check_string($params,$Y){
		/*
		 * проверка строки по хэшу
		 * */
		
		$string = array(
				$params['notification_type'],
				$params['operation_id'],
				$params['amount'],
				$params['currency'],
				$params['datetime'],
				$params['sender'],
				$params['codepro'],
				$Y['secret'],
				$params['label'],
				);

		$string = implode('&',$string);
			$string = sha1($string,true);

		return ($string==$params['sha1_hash']);
	}
	
	public function do_result($path){
		if(count($path)) Front_Order_Payment_Card_Helper::goto_index();
		if(!Front_Order_Payment_Card_Helper::keys_check()) exit();
		
		$params = $_POST;
		
		if($params['notification_type']!='card-incoming' || !$params['label']) exit();
		
        $Y = $this->registry['config']['yandex_money'];
                
        if(!$this->check_string($params,$Y)) exit();
        
		if(!$this->check_sum(
				$params['label'],
				$params['withdraw_amount']
				)) exit();
		
		$this->update_order($params['label']);
		$this->Front_Order_Mail_Card->send_letter($params['label']);
		
		echo "OK";
	}
		
	private function update_order($ai){
		mysql_query(sprintf("
				UPDATE
					orders
				SET
					status = 3,
					payed_on = NOW()
				WHERE
					ai = '%d';
				",
				$ai
				));
	}	
		
}
?>