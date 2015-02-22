<?php
Class Adm_Orders_Content Extends Common_Rq{

	private $registry;
	
	private $Adm_Orders_Goods;
	
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Adm_Orders_Goods = new Adm_Orders_Goods($this->registry);
	}
		
	public function order_check($num){
		$num = explode('-',$num);
		if(count($num)!=3) return false;
	
		$qLnk = mysql_query(sprintf("
				SELECT
					orders.*,
					users.name AS user_name,
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
	
	private function set_vars($order){
		$vars = array(
			'num' => $order['num'],
			'delivery_name' => $this->delivery_name($order),
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
			'discount' => $order['discount_percent']
		);
		
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);		
	}
	
	private function delivery_name($order){
		$delivery = Front_Order_Data_Delivery::get_methods($order['delivery_type']);
		
		return (isset($delivery['name'])) ? $delivery['name'] : false;		
	}
	
}
?>