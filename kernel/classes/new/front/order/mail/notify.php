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

		//только покупателям
		if($direction==1){
			$this->to_guests($html,$order);
			
		//только менеджерам	
		}elseif($direction==2){
			$this->to_managers($html,$order);
			
		//всем	
		}else{
			$this->to_guests($html,$order);
			$this->to_managers($html,$order);
		}
			
	}
	
	private function to_guests($html,$order){
		$this->registry['CL_mail']->send_mail(
				$order['user_email'],
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