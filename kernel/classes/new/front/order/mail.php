<?php
Class Front_Order_Mail{

	private $registry;
	
	private $Front_Order_Mail_Notify;
	private $Front_Order_Mail_Bill;
	private $Front_Order_Mail_Tech;
				
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Mail_Notify = new Front_Order_Mail_Notify($this->registry);
		$this->Front_Order_Mail_Bill = new Front_Order_Mail_Bill($this->registry);
		$this->Front_Order_Mail_Tech = new Front_Order_Mail_Tech($this->registry);		
	}	
		
	public function send_mail($order_num){
		//$this->Front_Order_Mail_Notify->send_letter();
			
		//if($order_vals['payment_method']==2) $this->Front_Order_Mail_Bill->send_letter();
			
		//$this->Front_Order_Mail_Tech->send_letter($order_id);		
	}
			
}
?>