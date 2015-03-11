<?php
Class Front_Order_Payment_Card_Done Extends Common_Rq{

	private $registry;
				
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	public function do_page(){
		$order = $this->get_order();
		
		$_SESSION['done_order_num'] = sprintf('%d/%d/%s',
					$order['id'],
					$order['user_num'],
					$order['payment_method']
					);;
		
		header('Location: /order/done/');
		exit();
	}
			
	public function get_order(){
		if(!Front_Order_Payment_Card_Helper::keys_check()) Front_Order_Payment_Card_Helper::goto_error();
		
		$qLnk = mysql_query(sprintf("
				SELECT
					id,
					user_num,
					payment_method			
				FROM
					orders
				WHERE
					ai = '%d'
					AND
					status = '3'
					",
				$_POST['InvId']
				));
		$order = mysql_fetch_assoc($qLnk);
		if(!$order) Front_Order_Payment_Card_Helper::goto_error();
		
		$R = $this->registry['config']['robokassa'];
		
		$crc  = strtoupper(md5(sprintf("%s:%s:%s:Shp_item=%s",
				$_POST['OutSum'],
				$_POST['InvId'],
				$R['pass'],
				$_POST['Shp_item']
				)));
	
		if($crc!=strtoupper($_POST['SignatureValue'])) Front_Order_Payment_Card_Helper::goto_error();
		
		return $order;
	}
		
}
?>