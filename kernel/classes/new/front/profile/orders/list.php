<?php
Class Front_Profile_Orders_List Extends Common_Rq{
	
	private $registry;
						
	public function __construct($registry){
		$this->registry = $registry;
	}	

	private function get_data($user){
		$orders = array();
		$qLnk = mysql_query(sprintf("
				SELECT
					*
				FROM
					orders
				WHERE
					user_id = '%d'
				ORDER BY
					id DESC;
				",
				$user['id']
				));
		while($o = mysql_fetch_assoc($qLnk)) $orders[$o['status']][$o['ai']] = $o;
		
		return $orders;
	}
	
	private function orders_lines($orders){
		ksort($orders,SORT_NUMERIC);
		$orders=array_reverse($orders);		
		
		$html = array();
		foreach($orders as $o){
			
			$o['num'] = sprintf('%d/%d/%s',
					$o['id'],
					$o['user_num'],
					$o['payment_method']
					);
			$o['link'] = sprintf('%d-%d-%s',
					$o['id'],
					$o['user_num'],
					$o['payment_method']
			);			
			
			$o['classes'] = ($o['status']==4 || $o['status']==5) ? 'cancelled' : '';
			
			$o['price'] = ($o['payment_method_id']) ? $o['overall_sum'] : $o['overall_price']; 
			
			$html[] = $this->do_rq('item',$o,true);
		}
		
		return implode('',$html);		
	}
	
	private function print_orders($orders){		
		$output = array();
		$statuses = range(1,5);
		
		foreach($statuses as $status){
			if(isset($orders[$status])){ 
				$output[$status] = $this->orders_lines($orders[$status]); 
			}elseif($status!=5){
				$output[$status] = $this->do_rq('blank',NULL,true);
			}
			
		}
		
		return $output;
	}
	
	public function print_list($user){
		$orders = $this->get_data($user);
		
		$vars = $this->print_orders($orders);
		
		$this->registry['CL_template_vars']->set('list',
				$this->do_rq('orders',$vars)
				);
	}
	
}
?>