<?php
Class Front_Order_Payment_Card Extends Common_Rq{

	private $registry;
				
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	private function check_roboxchange_success(){
		if(isset($_POST['SignatureValue'])){
			$qLnk = mysql_query("
					SELECT
					orders.*
					FROM
					orders
					WHERE
					orders.ai = '".$_POST['InvId']."'
					LIMIT 1;
					");
			if(mysql_num_rows($qLnk)>0){
				$order = mysql_fetch_assoc($qLnk);
	
				$login = ROBOKASSA_LG; //$mrh_login
				$pwd = ROBOKASSA_PW; //$mrh_pass1
				$unique_id = $_POST['InvId'];; //$inv_id
				$sum = $_POST['OutSum']; //$out_summ
				$code = $_POST['Shp_item'];	//$shp_item
	
				$crc  = strtoupper(md5("$sum:$unique_id:$pwd:Shp_item=$code"));
	
				if($crc==strtoupper($_POST['SignatureValue']) && $order['status']==3){
					$order_id = $order['id'].'/'.$order['user_num'].'/'.$order['payment_method'];
	
					$this->registry['order_info'] = $order;
	
					return true;
				}
			}
		}
		header('Location: /cart/order/card-error/');
		exit();
	}

	private function card_prepare_check(){
	
		if(isset($_GET['order_id']) && isset($this->registry['userdata']['id'])){
			$order_arr = explode('/',$_GET['order_id']);
			if(count($order_arr)==3){
				$qLnk = mysql_query("
						SELECT
						orders.*
						FROM
						orders
						WHERE
						orders.user_id = '".$this->registry['userdata']['id']."'
						AND
						orders.id = '".$order_arr[0]."'
						AND
						orders.user_num	= '".$order_arr[1]."'
						AND
						orders.payment_method = '".$order_arr[2]."'
						AND
						orders.by_card = 1
						AND
						orders.status = 1
						LIMIT 1;
						");
				if(mysql_num_rows($qLnk)>0){
					$this->registry['order_data'] = mysql_fetch_assoc($qLnk);
	
					setcookie('thecart','',time()-3600,'/');
					setcookie('cart_gift_id','',time()-3600,'/');
					setcookie('delivery_type','',time()-3600,'/');
	
					return true;
				}
			}
		}
	
		return false;
	}
	
	private function mk_roboxchange_data($order_id){
	
		$login = ROBOKASSA_LG; //$mrh_login
		$pwd = ROBOKASSA_PW; //$mrh_pass1
		$unique_id = $this->registry['order_data']['ai'];; //$inv_id
		$desc = 'Оплата заказа № '.$order_id.' в Бодибилдинг-Магазине'; //$inv_desc
		$sum = $this->registry['order_data']['overall_price'] - $this->registry['order_data']['from_account']; //$out_summ
		$code = 1;	//$shp_item
			
		$crc  = md5("$login:$sum:$unique_id:$pwd:Shp_item=$code");
	
		$this->registry['RD'] = array(
				'login' => $login,
				'sum' => $sum,
				'unique_id' => $unique_id,
				'desc' => $desc,
				'signature' => $crc,
				'code' => $code,
				'curr' => ROBOKASSA_CURR,
				'lang' => ROBOKASSA_LANG,
		);
	
	}	
}
?>