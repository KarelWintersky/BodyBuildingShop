<?php
Class Front_Order_Mail_Notify_Html Extends Common_Rq{
	
	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}	

	private function print_goods($order){
	
		$lines = array();
		foreach($order['goods'] as $g){
			$g['url'] = sprintf('%s%s/%s/%s',
					THIS_URL,
					$g['parent_alias'],
					$g['level_alias'],
					$g['alias']
			);
				
			$g['final_sum'] = $g['final_price']*$g['amount'];
				
			$lines[] = $this->do_rq('line',$g,true);
		}
	
		$a = array(
				'lines' => implode('',$lines),
				'sum' => Common_Useful::price2read(0),
				'discount_amount' => Common_Useful::price2read(0),
				'sum_with_discount' => Common_Useful::price2read(0)
		);
	
		return $this->do_rq('table',$a);
	}	
		
	/*private function additional_payment($order){
		if($order['from_account']!=$order['overall_price'] && $order['from_account']){
			return 'С Вашего личного счета удержано '.Common_Useful::price2read($order['from_account']).' руб., <b>к оплате '.Common_Useful::price2read($order['overall_price']-$order['from_account']).' руб.</b>';
		}
	}*/	
	
	public function print_html($order){
		
		$a = array(
				'num' => $order['num'],
				'date' => date('d.m.Y H:i',strtotime($order['made_on'])),
				'user_name' => $order['tech']['name'],
				'table' => $this->print_goods($order),
				'wishes' => $order['wishes']
				);
		
		return $this->do_rq('tpl',$a);
	}	
	
}
?>