<?php
Class Front_Order_Mail_Bill{
	
	private $registry;
	
	private $Front_Order_Bill;
	
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Bill = new Front_Order_Bill($this->registry);
	}	
			
	public function send_letter($order){
		if($order['payment_method_id']!=2 && $order['account_extra_payment']!=2) return false;
				
		$html = $this->Front_Order_Bill->to_letter($order['num']);
		
		$pdfmanager = new Pdfmanager($this->registry);
		$attach = $pdfmanager->fileCompose($html);
		
		$replace = array(
			'ORDER_NUM' => $order['num']
		);
		
		$mailer = new Mailer($this->registry,13,$replace,$order['user_email'],$attach);		
	}
}
?>