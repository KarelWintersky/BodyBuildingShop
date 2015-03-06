<?php
Class Front_Order_Mail_Tech{
	
	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}	

	public function send_letter($order){
		$lines = $this->get_lines($order);
		
		//служебное письмо о закзае
		$this->registry['CL_mail']->send_mail(
				explode('::',ADMINS_EMAILS),
				sprintf('В интернет-магазине новый заказ №%s%s',
						$order['num'],
						(strpos($order['num'],'П'!==false)) ? ' ПО ПРЕДОПЛАТЕ' : ''
						),
				$lines,
				false,
				false,
				'windows-1251'
		);		
		
		//уведомление об оплате со счета
		$this->from_account($order);		
	}
	
	private function from_account($order){
		if(!$order['from_account']) return false;
		
		$diff = $order['overall_sum'] - $order['from_account'];
		
		$text = sprintf('
				Оплачено со счета: %s
				<br />
				Осталось оплатить: %s
				',
				$order['from_account'],
				$diff
				);
		
		$this->registry['CL_mail']->send_mail(
				explode('::',ADMINS_EMAILS),
				sprintf('Заказ %s оплачен со счета %s',
						$order['num'],
						($diff) ? 'частично' : 'полностью'
				),
				$text,
				false,
				false,
				'windows-1251'
		);		
	}
	
	public function get_lines($order){		
		$delivery = Front_Order_Data_Delivery::get_methods($order['delivery_type']);
		
		$payment_id = ($order['account_extra_payment']) ? $order['account_extra_payment'] : $order['payment_method_id'];  
		$payment = Front_Order_Data_Payment::get_methods($payment_id);
		
		$order_line = array(
				$order['num'],
				$order['user_id'],
				$order['user_login'],
				date('d.m.Y',strtotime($order['made_on'])),
				date('H:i:s',strtotime($order['made_on'])),
				$order['delivery_costs'] + $order['nalog_costs'],
				$payment['tech_name'],
				$delivery['tech_name'],
				$order['overall_sum'],
				$order['tech']['email'],
				$order['tech']['name'],
				$order['tech']['address'],
				$_SERVER['HTTP_USER_AGENT'],
				$order['tech']['phone'],
				$order['wishes']
				);
		
		$order_line = $this->prepare_line($order_line);
		
		$lines = array();
		$lines[] = implode('::',$order_line);
		
		foreach($order['goods'] as $g){
			$goods_line = array(
					($g['barcode']) ? $g['barcode'] : 'any',
					$g['goods_feats_str'],
					$g['amount'],
					$g['discount'],
					$g['final_price'] - intval($g['final_price']*$order['discount_percent']/100) //цену указываем со скидкой (хранится без нее)
					);
						
			$goods_line = $this->prepare_line($goods_line);
			
			$lines[] = implode('::',$goods_line);			
		}
		
		return implode('<br>',$lines);		
	}
	
	private function prepare_line($line){
		foreach($line as $k => $v) if($v===false) $line[$k] = ' ';
		foreach($line as $k => $v) $line[$k] = str_replace('::','',$v);

		return $line;
	}
		

}
?>