<?php
Class Front_Profile_Orders_Pay Extends Common_Rq{
	
	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
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
					user_id = '%d'
					AND
					id = '%d'
					AND
					user_num = '%d'
					AND
					payment_method = '%s'
					AND
					by_card = 1
					AND
					payment_method_id <> '0'
				",
				$this->registry['userdata']['id'],
				$arr[0],
				$arr[1],
				mysql_real_escape_string($arr[2])
				));
		$order = mysql_fetch_assoc($qLnk);
		if(!$order) return false;

        $order['num'] = sprintf('%d/%d/%s',
            $order['id'],
            $order['user_num'],
            $order['payment_method']
        );

        //если заказ уже оплачен, редиректим на страницу успеха
        if($order['status']==3){
            $_SESSION['done_order_num'] = $order['num'];

            header('Location: /order/done/');
            exit();
        }

		$this->set_vars($order);
		
		return true;
	}
	
	private function set_vars($order){
	
		$this->registry['longtitle'] = sprintf('Оплата заказа № %s',$order['num']);

        $Y = $this->registry['config']['yandex_money'];

        $sum = $order['overall_sum'] - $order['from_account'];
        $sum = $sum/0.98; //возлагаем комиссию на покупателя

        $vars = array(
            'account_number' => $Y['account_number'],
            'comment' => sprintf('Бодибилдинг Магазин. Оплата заказа %s',$order['num']),
            'ai' => $order['ai'],
            'sum' => $sum
        );

		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}	
		
}
?>