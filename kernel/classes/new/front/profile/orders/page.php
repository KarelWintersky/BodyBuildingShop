<?php
Class Front_Profile_Orders_Page Extends Common_Rq{
	
	private $registry;
	
	private $Front_Profile_Orders_Page_Old;
	private $Front_Profile_Orders_Page_New;
	private $Front_Profile_Orders_Goods;
						
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Profile_Orders_Page_Old = new Front_Profile_Orders_Page_Old($this->registry);
		$this->Front_Profile_Orders_Page_New = new Front_Profile_Orders_Page_New($this->registry);
		$this->Front_Profile_Orders_Goods = new Front_Profile_Orders_Goods($this->registry);
	}	

	public function check_order($num){
		$arr = explode('-',$num);
		if(count($arr)!=3) return false;
		
		$qLnk = mysql_query(sprintf("
				SELECT
					*
				FROM
					orders
				WHERE
					id = '%d'
					AND
					user_num = '%d'
					AND
					payment_method = '%s'
					AND
					user_id = '%d'
				",
				$arr[0],
				$arr[1],
				mysql_real_escape_string($arr[2]),
				$this->registry['userdata']['id']
				));
		$order = mysql_fetch_assoc($qLnk);
		if(!$order) return false;
		
		$order['num'] = sprintf('%d/%d/%s',
				$order['id'],
				$order['user_num'],
				$order['payment_method']
				);
		
		$delivery = Front_Order_Data_Delivery::get_methods($order['delivery_type']);
		$order['delivery_type_name'] = $delivery['name'];
		
		$order = ($order['payment_method_id'])
			? $this->Front_Profile_Orders_Page_New->do_extend($order)
			: $this->Front_Profile_Orders_Page_Old->do_extend($order);
				
		$this->set_vars($order);
		
		return true;		
	}
	
	private function set_vars($order){
		
		$this->registry['longtitle'] = sprintf('Заказ № %s',$order['num']);
		
		$vars = array(
				'num' => $order['num'],
				'features' => $this->do_rq('features',$order),
				'numbers' => $this->do_rq('numbers',$order['numbers']),
				'goods' => $this->Front_Profile_Orders_Goods->print_goods($order['num'])
				);
		
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
	
}
?>

		<?if($this->registry['orderdata']['status']==1 && $this->registry['orderdata']['payment_method']!='H' && $this->registry['orderdata']['payment_method']!='Н'):?>
			<br><br>
		<?endif;?>


	/*
	
	public function print_account_orders(){
		$statuses = array(
				1 => 'сформирован',
				2 => 'оплачен',
				3 => 'отменен',
		);
		$qLnk = mysql_query("
				SELECT
				account_orders.*
				FROM
				account_orders
				WHERE
				account_orders.user_id = '".$this->registry['userdata']['id']."'
				ORDER BY
				account_orders.createdon DESC;
				");
		if(mysql_num_rows($qLnk)>0){
			while($o = mysql_fetch_assoc($qLnk)){
				$o['num'] = $o['id'].'/'.$o['user_num'].'/A';
				$o['s'] = $statuses[$o['status']];
				$this->item_rq('account_order',$o);
			}
		}else{
			echo '<td class="no_orders" colspan="4">Нет ни одного заказа</td>';
		}
	}	*/

