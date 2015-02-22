<?php
Class Front_Order_Payment_Card_Done{

	private $registry;
				
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	public function do_page(){

		$vars = array(
			'order_num' => '',
			'overall_sum' => '',
		);
		
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
		
	public function success_check(){
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
					
		
	}
		
}
?>