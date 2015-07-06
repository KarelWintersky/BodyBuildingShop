<?php
Class Front_Order_Payment_Card_Result{

	private $registry;
	
	private $Front_Order_Mail_Card;
				
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Mail_Card = new Front_Order_Mail_Card($this->registry);
	}	
	
	public function do_result($path){
		w($_POST);
		echo 2;
		
		/*if(count($path) || !Front_Order_Payment_Card_Helper::keys_check()) Front_Order_Payment_Card_Helper::goto_index();
		
        $R = $this->registry['config']['robokassa'];
                
		$crc = strtoupper(md5(sprintf("%s:%s:%s:Shp_item=%s",
				$_POST['OutSum'],
				$_POST['InvId'],
				$R['pass2'],
				$_POST['Shp_item']
				)));
		
		if($crc!=strtoupper($_POST['SignatureValue'])) exit();
		
		$this->update_order($_POST['InvId']);
		$this->Front_Order_Mail_Card->send_letter($_POST['InvId']);
		
		echo sprintf("OK%s\n",
				$_POST['InvId']
				);		*/
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