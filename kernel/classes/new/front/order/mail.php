<?php
Class Front_Order_Mail{

	private $registry;
	
	private $Front_Order_Mail_Notify;
	private $Front_Order_Mail_Bill;
	private $Front_Order_Mail_Tech;
	private $Front_Order_Mail_Data;
				
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Mail_Notify = new Front_Order_Mail_Notify($this->registry);
		$this->Front_Order_Mail_Bill = new Front_Order_Mail_Bill($this->registry);
		$this->Front_Order_Mail_Tech = new Front_Order_Mail_Tech($this->registry);		
		$this->Front_Order_Mail_Data = new Front_Order_Mail_Data($this->registry);		
		
		$Common_Mail = new Common_Mail($this->registry);		
	}	
		
	public function send_mail($order_num){
		$order = $this->Front_Order_Mail_Data->get_data($order_num);
		
		$this->Front_Order_Mail_Notify->send_letter($order);
			
		$this->Front_Order_Mail_Bill->send_letter($order);
		
		$this->Front_Order_Mail_Tech->send_letter($order);		
	}
	
	public function send_only_bill(){
		/*
		 * вызывается из админки
		 * из профиля заказа (послать квитанцию)
		 * */
		
		$order = $this->Front_Order_Mail_Data->get_data($_POST['num']);
		
		$this->Front_Order_Mail_Bill->send_letter($order);
	}

	public function send_only_message(){
		$order = $this->Front_Order_Mail_Data->get_data($_POST['num']);
		$this->Front_Order_Mail_Tech->send_letter($order);
		$this->Front_Order_Mail_Notify->send_letter($order,1);
	}	
	
}
?>