<?php
Class Front_Order_Done{

	private $registry;
				
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	private function cart_done_msg(){
	
		$order_vals = $_POST['order_vals'];
	
		$goods_arr = array();
		foreach($order_vals['goods_data'] as $arr){
			$goods_arr[$arr['goods_id']] = $arr['full_name'];
		}
	
		$ostatki = array();
		$qLnk = mysql_query("
				SELECT
				ostatki.*
				FROM
				ostatki
				WHERE
				ostatki.goods_id IN (".implode(',',array_keys($goods_arr)).")
				");
		while($o = mysql_fetch_assoc($qLnk)){
			$ostatki[] = $goods_arr[$o['id']];
		}
	
		if(count($ostatki)>0){
			$additional = 'Поскольку в вашей корзине присутствует товар <b>'.implode(', ',$ostatki).'</b>, количество которого строго ограничено и резервируется под заказ, то Вам будет доступна только предоплата.<br><br>Заказ Вы должны будете оплатить в течении '.REZERV_ORDER_DAYS.' дней и ОБЯЗАТЕЛЬНО сообщить нам о факте оплаты е-мэйлом. В противном случае, через '.REZERV_ORDER_DAYS.' дней если от вас не поступят деньги или уведомление об оплате - резерв на товар будет снят и он вернется в продажу.';
		}else{
			$additional = '';
		}
	
		$user_address = $this->registry['logic']->implode_address($this->registry['userdata']);
	
		$replace_arr = array(
				'ORDER_NUM' => $this->registry['order_id'],
				'OVERALL_SUM' => intval($order_vals['overall_price']-$this->registry['from_account']),
				'USER_NAME' => $this->registry['userdata']['name'],
				'USER_ADDRESS' => $user_address,
				'USER_MAIL' => $this->registry['userdata']['email'],
				'USER_PHONE' => $order_vals['phone'],
				'FREE_DELIVERY_SUM' => FREE_DELIVERY_SUM,
				'ADDITIONAL' => $additional
		);
	
		if($order_vals['payment_method']==2 && $order_vals['delivery_type']==1){ //квитанция
			$msg_id = 1;
		}elseif($order_vals['payment_method']==3 && $order_vals['delivery_type']==1){ //WM
			$msg_id = 2;
		}elseif($order_vals['payment_method']==6 && $order_vals['delivery_type']==1){ //личный счет
			$msg_id = 3;
			$replace_arr['OVERALL_SUM'] = $this->registry['from_account'];
		}elseif($order_vals['payment_method']==1 && $order_vals['delivery_type']==1){ //наложка
			$msg_id = 4;
		}elseif($order_vals['payment_method']==5 && $order_vals['delivery_type']==2){ //оплата курьеру
			$msg_id = 5;
		}elseif($order_vals['payment_method']==2 && $order_vals['delivery_type']==2){ //квитанция курьер
			$msg_id = 6;
		}elseif($order_vals['payment_method']==3 && $order_vals['delivery_type']==2){ //WM курьер
			$msg_id = 7;
		}elseif($order_vals['payment_method']==6 && $order_vals['delivery_type']==2){ //личный счет курьер
			$msg_id = 8;
			$replace_arr['OVERALL_SUM'] = $this->registry['from_account'];
		}elseif($order_vals['delivery_type']==3){ //транспортная компания
			$msg_id = 9;
		}elseif($order_vals['delivery_type']==4){ //самовывоз
			$msg_id = 10;
		}
	
		$qLnk = mysql_query("SELECT order_msgs.text FROM order_msgs WHERE order_msgs.id = '".$msg_id."';");
		$cart_done_msg = (mysql_num_rows($qLnk)>0) ? mysql_result($qLnk,0) : '';
	
		foreach($replace_arr as $find => $replace){
			$cart_done_msg = str_replace('{'.$find.'}', $replace, $cart_done_msg);
		}
	
		$this->registry['cart_done_msg'] = $cart_done_msg;
	
		if($msg_id==1 || $msg_id==6){
			$this->registry['do_bill'] = true;
		}
	}			
}
?>