<?php
Class Front_Order_Payment_Card{

	private $registry;
				
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	public function do_prepare(){
		if(!isset($_GET['id'])) Front_Order_Payment_Card_Helper::goto_error();
		if (isset($_GET['callback']) && $_GET['callback'] == 'yandex') {
		    $sleep_required = true;
		} else { $sleep_required = false; }

		
		$order_id = trim($_GET['id']);
		if(!$order_id) Front_Order_Payment_Card_Helper::goto_error();

		$num = explode('/',$order_id);
		if(count($num)!=3) Front_Order_Payment_Card_Helper::goto_error();

		$qLnk = mysql_query(sprintf("
				SELECT
					*
				FROM
					orders
				WHERE
					id = '%d'
					AND
					user_num	= '%d'
					AND
					payment_method = '%s'
					AND
					by_card = 1
				",
				$num[0],
				$num[1],
				mysql_real_escape_string($num[2])
		));
		$order = mysql_fetch_assoc($qLnk);
		if(!$order) Front_Order_Payment_Card_Helper::goto_error();

		//если заказ уже оплачен, редиректим на страницу успеха
		if($order['status']==3){
			$_SESSION['done_order_num'] = $order_id;
			
			header('Location: /order/done/');
			exit();			
		}
		
		if ($sleep_required) {
die("
<html>
    <head>
        <meta http-equiv='refresh' content='10;url=/order/card/prepare/?id={$order_id}' />
    </head>
    <script>
	var timer = setTimeout(function()
	{ window.location='/order/card/prepare/?id={$order_id}'; }, 10000);
    </script>
    <body>
	<h1>Подождите пожалуйста, обрабатываем ответ от платёжной системы... Это займет примерно 10 секунд.</h1>
    </body>
</html>");
//		    header('Location: /order/card/prepare/?id=' . $order_id );
		} else {
		    $Y = $this->registry['config']['yandex_money'];

	            $sum = $order['overall_sum'] - $order['from_account'];
	            $sum = $sum/0.98; //возлагаем комиссию на покупателя

		    $vars = array(
			'account_number' => $Y['account_number'],
			'comment' => sprintf('Бодибилдинг Магазин. Оплата заказа %s',$order_id),
			'ai' => $order['ai'],
			'sum' => $sum,
			'order_id' => $order_id
		    );
		
		    foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
		    $this->registry->set('longtitle','Оплата заказа');
		}
	}
	
}
?>