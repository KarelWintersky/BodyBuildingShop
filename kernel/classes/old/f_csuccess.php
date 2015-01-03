<?
Class f_Csuccess{

	private $registry;

	public function pgc(){}

	public function __construct($registry){
		$this->registry = $registry;
		$this->registry->set('f_csuccess',$this);
	}

	public function path_check(){

		$path_arr = $this->registry['route_path'];
		$this->registry['template']->set('no_tpl',true);

		if(count($path_arr)==0 && count($_POST)>0){
			$this->mk_card_result();
			$this->registry['f_404'] = false;
			
			$this->registry['CL_css']->set(array(
					'cart',
			));			
			
			return true;
		}else{
			header('Location: /');
			exit();
		}

	}

	private function mk_card_result(){

		$pw_2 = ROBOKASSA_PW_2;
		$out_summ = $_POST['OutSum'];
		$inv_id = $_POST['InvId'];
		$shp_item = $_POST['Shp_item'];
		$crc = $_POST['SignatureValue'];
			$crc = strtoupper($crc);

		$new_crc = strtoupper(md5("$out_summ:$inv_id:$pw_2:Shp_item=$shp_item"));

		if($new_crc!=$crc){
			exit();
		}

		$this->write_success_order($inv_id);
		$this->success_send($inv_id);

		echo "OK$inv_id\n";

	}

	public function success_send($ai){
		$qLnk = mysql_query("
							SELECT
								orders.*,
								users.email AS user_email
							FROM
								orders
							INNER JOIN users ON users.id = orders.user_id
							WHERE
								orders.ai = '".$ai."';
							");
		if(mysql_num_rows($qLnk)>0){
			$order = mysql_fetch_assoc($qLnk);

			$order_id = $order['id'].'/'.$order['user_num'].'/'.$order['payment_method'];

			//админское письмо
			$emails = explode('::',ADMINS_EMAILS);
			if(count($emails)>0){
				foreach($emails as $admin_mail){

					$subj = 'Заказ '.$order_id.' оплачен по банковской карте';
					$txt = 'Заказ '.$order_id.' оплачен по банковской карте на сумму '.$order['overall_price'].' руб.';
					$replace_arr['ORDER_NUM_SUBJ'] = iconv('utf-8','windows-1251',$subj);
						$replace_arr['ORDER_NUM_TEXT'] = iconv('utf-8','windows-1251',$txt);
					$mailer = new Mailer($this->registry,32,$replace_arr,$admin_mail,false,'windows-1251');

				}
			}

			//уведомление юзеру
			$replace_arr = array(
				'ORDER_NUM' => $order_id,
				'OVERALL_PRICE' => $order['overall_price'],
				'DELIVERY_COMMENT' => ($order['delivery_type']==1) ? 'В ближайшее время заказ будет передан в обработку. После отправки Вы получите уведомление где будет указана точная дата отправки и номер отправления для отслеживания посылки на сайте почты России.' : 'Если заказ Был сделан до 12 часов то курьер свяжется с Вами в течении дня. В противном случае - на следующий рабочий день после заказа. (За исключением случаев форс-мажора или невозможности связаться с Вами по указанному Вами телефону).',
			);
			$mailer = new Mailer($this->registry,35,$replace_arr,$order['user_email']);

		}
	}

	private function write_success_order($ai){
		mysql_query("
					UPDATE
						orders
					SET
						orders.status = 3,
						orders.payed_on = NOW()
					WHERE
						orders.ai = '".$ai."';
					");

		//смотрим, есть ли что по остаткам и резервам
		$qLnk = mysql_query("
							SELECT
								orders.id,
								orders.user_num,
								orders.payment_method
							FROM
								orders
							WHERE
								orders.ai = '".$ai."'
							LIMIT 1;
							");
		if(mysql_num_rows($qLnk)>0){
			$order_arr = mysql_fetch_assoc($qLnk);
			Settings::order_apply($order_arr);
		}
	}

}
?>