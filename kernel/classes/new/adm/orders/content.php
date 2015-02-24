<?php
Class Adm_Orders_Content Extends Common_Rq{

	private $registry;
	
	private $Adm_Orders_Goods;
	private $Adm_Orders_Customer;
	
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Adm_Orders_Goods = new Adm_Orders_Goods($this->registry);
		$this->Adm_Orders_Customer = new Adm_Orders_Customer($this->registry);
	}
		
	public function order_check($num){
		$num = explode('-',$num);
		if(count($num)!=3) return false;
	
		$qLnk = mysql_query(sprintf("
				SELECT
					orders.*,
					users.name AS user_name,
					users.email AS user_email,
					users.phone AS user_phone,
					users.zip_code AS zip_code,
					users.region AS region,
					users.district AS district,
					users.city AS city,
					users.street AS street,
					users.house AS house,
					users.corpus AS corpus,
					users.flat AS flat
				FROM
					orders
				LEFT OUTER JOIN users ON users.id = orders.user_id
				WHERE
					orders.id = '%d'
					AND
					orders.user_num = '%d'
					AND
					orders.payment_method = '%s'
				",
				$num[0],
				$num[1],
				mysql_real_escape_string($num[2])
				));
		$order = mysql_fetch_assoc($qLnk);
		if(!$order) return false;
		
		$order['num'] = sprintf('%d/%d/%s',
				$order['id'],
				$order['user_num'],
				$order['payment_method']
				);
		
		$this->set_vars($order);
		
		return true;
	}
	
	private function price_values($order){
		$sum = ($order['payment_method_id']) ? $order['sum_with_discount'] : $order['sum'];
			$sum = ($sum) ? Common_Useful::price2read($sum) : '-';
		
		$overall = ($order['payment_method_id']) ? $order['overall_sum'] : $order['overall_price'];

		$discount = ($order['payment_method_id']) ? $order['discount_percent'] : $order['discount'];
		
		$values = array(
				'discount' => ($discount) ? $discount : 0,
				'sum' => $sum,
				'delivery_costs' => ($order['delivery_costs']) ? Common_Useful::price2read($order['delivery_costs']) : '-',
				'nalog_costs' => ($order['nalog_costs']) ? Common_Useful::price2read($order['nalog_costs']) : '-',
				'overall_costs_label' => ($order['from_account']) 
					? 'Всего / со счета / доплатить'
					: 'Всего',		
				'overall_costs' => ($order['from_account']) ?
					sprintf('%s / %s / %s',
							Common_Useful::price2read($overall),
							Common_Useful::price2read($order['from_account']),
							Common_Useful::price2read($overall - $order['from_account'])
							)
					: Common_Useful::price2read($overall),		
				);
		
		return $values;
	}
		
	private function set_vars($order){
		$vars = array(
			'num' => $order['num'],
			'delivery_name' => $this->delivery_name($order),
			'payment_name' => $this->payment_name($order),
			'mail_actions' => $this->do_rq('mail',$order),
			'statuses' => Adm_Orders_Helper::statuses_options($order['status']),
			'made_on' => date('d.m.Y H:i',strtotime($order['made_on'])),
			'sent_on' => ($order['sent_on'] && $order['sent_on']!='0000-00-00') 
				? date('d.m.Y',strtotime($order['sent_on']))
				: false,
			'payed_on' => ($order['payed_on'] && $order['payed_on']!='0000-00-00')
				? date('d.m.Y',strtotime($order['payed_on']))
				: false,
			'goods' => $this->Adm_Orders_Goods->goods_list($order),
			'postnum' => ($order['postnum']) ? $order['postnum'] : false,			
			'wishes' => ($order['wishes']) ? $order['wishes'] : false,			
			'comment' => ($order['comment']) ? $order['comment'] : false,
			'customer' => $this->Adm_Orders_Customer->print_block($order)
		);
		
		$vars = $vars + $this->price_values($order);
		
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);		
	}
	
	private function payment_name($order){
		if(!$order['payment_method_id']) return $order['payment_method'];
		
		$payemnt = Front_Order_Data_Payment::get_methods($order['payment_method_id']);
		
		$extra_payment = ($order['account_extra_payment']) 
			? Front_Order_Data_Payment::get_methods($order['account_extra_payment'])
			: false;
		
		if($extra_payment) $extra_payment = $extra_payment['short_name'];
		
		return sprintf('%s (%s%s)',
				$order['payment_method'],
				$payemnt['short_name'],
				($extra_payment) ? sprintf(', доплата - %s',$extra_payment) : ''
				);
	}
	
	private function delivery_name($order){
		$delivery = Front_Order_Data_Delivery::get_methods($order['delivery_type']);
		
		return (isset($delivery['name'])) ? $delivery['name'] : false;		
	}
	
}
?>