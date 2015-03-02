<?php
Class Front_Order_Mail_Notify{
	
	private $registry;
	
	private $Front_Order_Mail_Notify_Html;
	
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Mail_Notify_Html = new Front_Order_Mail_Notify_Html($this->registry);
	}	
						
	public function send_letter($order,$direction = 0){

		//только покупателям
		if($direction==1){
			$this->to_guests(
					$this->Front_Order_Mail_Notify_Html->print_html($order,false),
					$order);
			
		//только менеджерам	
		}elseif($direction==2){
			$this->to_managers(
					$this->Front_Order_Mail_Notify_Html->print_html($order,true),
					$order);
			
		//всем	
		}else{
			$this->to_guests(
					$this->Front_Order_Mail_Notify_Html->print_html($order,false),
					$order);
			$this->to_managers(
					$this->Front_Order_Mail_Notify_Html->print_html($order,true),
					$order);
		}
			
	}
	
	private function to_guests($html,$order){
		$this->registry['CL_mail']->send_mail(
				$order['tech']['email'],
				sprintf('Бодибилдинг-Магазин: заказ №%s',$order['num']),
				$html
				);
	}

	private function to_managers($html,$order){
		$emails = explode('::',ADMINS_EMAILS);
				
		$this->registry['CL_mail']->send_mail(
				$emails,
				sprintf('Бодибилдинг-Магазин: заказ №%s',$order['num']),
				$html
		);		
	}		
	
}
?>