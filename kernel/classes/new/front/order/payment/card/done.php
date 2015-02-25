<?php
Class Front_Order_Payment_Card_Done{

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
		);
		
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
		
	public function get_order(){
		if(!Front_Order_Payment_Card_Helper::keys_check()) Front_Order_Payment_Card_Helper::goto_error();
		
		$qLnk = mysql_query(sprintf("
				SELECT
					*
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