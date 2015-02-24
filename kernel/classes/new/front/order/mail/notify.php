<?php
Class Front_Order_Mail_Notify{
	
	private $registry;
	
	private $Front_Order_Mail_Notify_Html;
	
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Mail_Notify_Html = new Front_Order_Mail_Notify_Html($this->registry);
	}	
						
	public function send_letter($order,$direction = 0){
					
		$html = $this->Front_Order_Mail_Notify_Html->print_html($order);
		echo $html;exit();

		//только покупателям
		if($direction==1){
			$this->to_guests($tpl_id,$replace,$order);
			
		//только менеджерам	
		}elseif($direction==2){
			$this->to_managers($tpl_id,$replace,$order);
			
		//всем	
		}else{
			$this->to_guests($tpl_id,$replace,$order);
			$this->to_managers($tpl_id,$replace,$order);
		}
			
	}
	
	private function to_guests($tpl_id,$replace,$order){
		$mailer = new Mailer($this->registry,$tpl_id,$replace,$order['user_email']);
	}

	private function to_managers($tpl_id,$replace,$order){
		$emails = explode('::',ADMINS_EMAILS);
		
		$replace['ADMIN_ORDER_SUM'] = '<span style="color:#999;font-size:13px;font-weight:normal;">'.$replace['OVERALL_PRICE'].' руб.<span>';
		
		foreach($emails as $admin_mail)
			$mailer = new Mailer($this->registry,$tpl_id,$replace,$admin_mail);	
	}		
	
}
?>