<?php
Class Front_Order_Mail_Notify_Table Extends Common_Rq{
	
	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}	

	private function styles($key){
		$styles = array(
				'th_style' => 'background:#f2f2f2;border:1px solid #ccc;color:#999;text-align:center;font-weight:normal;padding:5px 0;',
				'td_right' => 'border:1px solid #ccc;padding:5px 5px 5px 0;text-align:right;'
				);
		
		return $styles[$key];
	}
	
	public function print_goods($order){
	
		$lines = array();
		foreach($order['goods'] as $g){
			
			$g['url'] = sprintf('%s%s/%s/%s',
					THIS_URL,
					$g['parent_alias'],
					$g['level_alias'],
					$g['alias']
			);
				
			$g['feature_label'] = ($g['parent_parent_id']==4) ? 'Размер' : 'Вкус' ;
			
			$g['final_sum'] = $g['final_price']*$g['amount'];
				
			$g['td_right'] = $this->styles('td_right');
			
			$lines[] = $this->do_rq('line',$g,true);
		}
	
		$a = array(
			'th_style' => $this->styles('th_style'),
			'td_right' => $this->styles('td_right'),	
			'lines' => implode('',$lines),
			'nalog_costs' => $order['nalog_costs'],	
			'delivery_costs' => $order['delivery_costs'],	
			'overall_sum' => $order['overall_sum'],	
			'sum_full' => $order['sum_full'],	
			'sum_with_discount' => $order['sum_with_discount'],	
			'discount_amount' => $order['sum_full'] - $order['sum_with_discount'],
			'discount_percent' => $order['discount_percent']
		);
	
		return $this->do_rq('table',$a);
	}	
			
}
?>