<?
	Class f_Cart{

		private $registry;

		public function pgc(){}

		public function __construct($registry){
			$this->registry = $registry;
			$this->registry->set('f_cart',$this);
			
			$this->store_params();
		}
		
		public function path_check(){

			if(isset($_SESSION['user_id'])){

				if(count($path_arr)==1 && $path_arr[0]=='order'){
					
				}elseif(count($path_arr)==2 && $path_arr[0]=='order' && $path_arr[1]=='check'){
					if(!isset($_COOKIE['thecart']) || $_COOKIE['thecart']==''){header('Location: /cart/');exit();}
					$this->registry['template']->add2crumbs('order','Оформление заказа');
					$this->registry['template']->set('c','cart/check');
					$this->registry['longtitle'] = 'Проверка заказа';
					$this->check_if_spb();
					$this->wishesphone_2_cookie();		
					
					return true;
				}elseif(count($path_arr)==2 && $path_arr[0]=='order' && $path_arr[1]=='done'){			
					
					if(!isset($_COOKIE['thecart']) || $_COOKIE['thecart']==''){
						header('Location: /cart/');
						exit();
					}else{

						if($_POST['order_vals']['payment_method']==4 || $_POST['order_vals']['payment_method']==7){ //если оплата картой - отдельно обрабатываем
							$this->write_order(true);
						}else{
							$this->registry['template']->add2crumbs('order','Оформление заказа');
							$this->registry['template']->set('c','cart/done');
							$this->registry['longtitle'] = 'Заказ совершен';

							$this->write_order(false);
							$this->cart_done_msg();
						}

					}
					
					
					return true;
				}elseif(count($path_arr)==2 && $path_arr[0]=='order' && $path_arr[1]=='card-prepare' && $this->card_prepare_check()){

					$this->registry['template']->add2crumbs('order','Оформление заказа');
					$this->registry['template']->set('c','cart/card-prepare');
					$this->registry['longtitle'] = 'Оплата заказа';

					$this->mk_roboxchange_data($_GET['order_id']);
					$this->registry['logic']->send_order($_GET['order_id'],true,true);
					$this->registry['logic']->admins_notify($_GET['order_id']);				
					
					return true;
				}elseif(count($path_arr)==2 && $path_arr[0]=='order' && $path_arr[1]=='card-done' && $this->check_roboxchange_success()){

					$this->registry['template']->add2crumbs('order','Оформление заказа');
					$this->registry['template']->set('c','cart/card-done');
					$this->registry['longtitle'] = 'Оплата прошла';					
					
					return true;
				}elseif(count($path_arr)==2 && $path_arr[0]=='order' && $path_arr[1]=='card-error'){

					$this->registry['template']->add2crumbs('order','Оформление заказа');
					$this->registry['template']->set('c','cart/card-error');
					$this->registry['longtitle'] = 'Ошибка при проведении оплаты';				
					
					return true;
				}

			}else{
				header('Location: /auth/?src=1');
				exit();
			}

			$this->registry['f_404'] = true;
			return false;
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

		private function write_order($card = false){

			$order_vals = $_POST['order_vals'];

			$user_id = $this->registry['userdata']['id'];

			$qLnk = mysql_query("SELECT IFNULL(MAX(orders.user_num),0)+1 FROM orders WHERE orders.user_id = '".$user_id."'");
			$user_num = mysql_result($qLnk,0);
			
			if(in_array($order_vals['payment_method'],array(1))){
				$payment_method = 'Н';
				$param_name = 'LAST_ORDER_N';
				$local_discount = 0;
			}elseif(in_array($order_vals['payment_method'],array(3))){
				$payment_method = 'W';
				$param_name = 'LAST_ORDER_W';
				$local_discount = 0;
			}else{
				$payment_method = 'П';
				$param_name = 'LAST_ORDER_P';
				$local_discount = 0;
			}

			$this->registry['payment_method'] = $order_vals['payment_method'];

			$overall_discount = OVERALL_DISCOUNT + $this->registry['userdata']['personal_discount'] + $local_discount;

			$qLnk = mysql_query("SELECT (params.value+1) FROM params WHERE params.name = '".$param_name."';");
			$id = mysql_result($qLnk,0);

			$order_status = (in_array($order_vals['payment_method'],array(6))) ? 3 : 1;
			$payed_on = (in_array($order_vals['payment_method'],array(6))) ? "NOW()" : "'0000-00-00'";

			if($order_vals['payment_method']==6){
				$from_account = $order_vals['overall_price'];
			}else{
				$from_account = (isset($order_vals['from_account'])) ? $order_vals['from_account'] : 0;
			}
			$this->registry['from_account'] = $from_account;

			$pay2courier = ($order_vals['payment_method']==5) ? 1 : 0;

			$by_card = ($card) ? 1 : 0;

			mysql_query("
						INSERT INTO
							orders
							(id,
								status,
									user_num,
										made_on,
											payed_on,
												delivery_costs,
													sum,
														overall_price,
															payment_method,
																user_id,
																	discount,
																		wishes,
																			delivery_type,
																				from_account,
																					pay2courier,
																						phone_number,
																							by_card,
																								coupon_discount)
							VALUES
							('".$id."',
								'".$order_status."',
									'".$user_num."',
										NOW(),
											".$payed_on.",
												'".$order_vals['delivery_costs']."',
													'".$order_vals['sum']."',
														'".$order_vals['overall_price']."',
															'".$payment_method."',
																'".$user_id."',
																	'".$overall_discount."',
																		'".$order_vals['wishes']."',
																			'".$order_vals['delivery_type']."',
																				'".$from_account."',
																					'".$pay2courier."',
																						'".$order_vals['phone']."',
																							'".$by_card."',
																								'".$order_vals['coupon_discount']."');
						");

			mysql_query("UPDATE params SET params.value = '".$id."' WHERE params.name = '".$param_name."'");

			$order_id = $id.'/'.$user_num.'/'.$payment_method;
			$this->registry['order_id'] = $order_id;

			//coupon truncate
			mysql_query("
						UPDATE
							coupons
						SET
							coupons.usedon = NOW(),
							coupons.usedby = '".$user_id."',
							coupons.order_id = '".$order_id."',
							coupons.status = 4
						WHERE
							coupons.hash = '".$order_vals['coupon']."'
						");

			$goods_ids = array();
			$goods_q_arr = array();
			
			foreach($order_vals['goods_data'] as $goods_rec){
					$goods_ids[$goods_rec['goods_id']] = (isset($goods_ids[$goods_rec['goods_id']])) ? ($goods_ids[$goods_rec['goods_id']]+$goods_rec['amount']) : $goods_rec['amount'];
					
					if($goods_rec['color']>0){
						$qLnk = mysql_query(sprintf("SELECT IFNULL(name,'') FROM features WHERE id = '%d'",$goods_rec['color']));
						$color = mysql_result($qLnk,0);
					}else $color = '';
															
					$goods_q_arr[] = "
								('".$order_id."',
									'".$goods_rec['barcode']."',
										'".$goods_rec['full_name']."',
											'".$goods_rec['packing']."',
													'".$goods_rec['amount']."',
														'".$goods_rec['price']."',
															'".$goods_rec['discount']."',
																'".$goods_rec['price']."',
																	'".$color."')
										";
			}
			
			mysql_query("
						INSERT INTO
							orders_goods
							(order_id,
								goods_barcode,
									goods_full_name,
										goods_packing,
											amount,
												price,
													discount,
														final_price,
															goods_feats_str)
							VALUES
							".implode(', ',$goods_q_arr)."
						");
						
			$ostatki_flag = $this->do_ostatki($order_id,$goods_ids);

			mysql_query("UPDATE users SET users.my_account = (users.my_account - ".$from_account.") WHERE users.id = '".$user_id."'");
			if($order_vals['payment_method']==6){
				//если предоплата сразу же со счета, пересчитываем скиду и наложку. А также популярность
				$order_arr = explode('/',$order_id);
				$blocks = new Blocks($this->registry,false);
				$mail_nalog = $blocks->order_nalog($order_arr,1);
				$mail_discount = $blocks->mk_discount($order_arr,1);

				if($mail_nalog || $mail_discount){
					$this->mail_user_data_change($mail_nalog,$mail_discount);
				}

				$this->goods_rate($goods_ids);
			}

			//!!!!!!!!!!!!111111
			if($card){
				header('Location: /cart/order/card-prepare/?order_id='.$order_id.'&tp='.$_POST['order_vals']['payment_method']);
				exit();
			}else{
				$this->registry['logic']->send_order($order_id,true,true);

				$this->send_ostatok_notify($ostatki_flag,$order_id);

				if($order_vals['payment_method']==2){
					$this->registry['logic']->send_bill($order_id);
				}

				$this->registry['logic']->admins_notify($order_id);

				setcookie('thecart','',time()-3600,'/');
				setcookie('cart_gift_id','',time()-3600,'/');
				setcookie('delivery_type','',time()-3600,'/');
			}

		}

		private function goods_rate($goods_ids){

			foreach($goods_ids as $id => $incr){
				mysql_query("
							UPDATE
								goods
							SET
								goods.popularity_index = goods.popularity_index + ".$incr."
							WHERE
								goods.id = ".$id.";
							");
			}

		}

		private function mail_user_data_change($mail_nalog,$mail_discount){
			if($mail_nalog && $mail_discount){
				$tpl_id = 9;
			}elseif($mail_nalog){
				$tpl_id = 5;
			}elseif($mail_discount){
				$tpl_id = 8;
			}

			$qLnk = mysql_query("
								SELECT
									users.personal_discount,
									users.max_nalog
								FROM
									users
								WHERE
									users.id = '".$this->registry['userdata']['id']."';
								");
			if(mysql_num_rows($qLnk)>0){

				$ua = mysql_fetch_assoc($qLnk);

				$replace_arr = array(
					'USER_NAME' => $this->registry['userdata']['name'],
					'MAX_NALOG' => $ua['max_nalog'],
					'PERSONAL_DISCOUNT' => $ua['personal_discount'],
					'SITE_URL' => THIS_URL
				);

				$mailer = new Mailer($this->registry,$tpl_id,$replace_arr,$this->registry['userdata']['email']);
			}

		}

		private function wishesphone_2_cookie(){
			$w = (isset($_POST['order_vals']['wishes'])) ? $_POST['order_vals']['wishes'] : '';
			setcookie('cart_wishes',$w,time()+3600,'/');

			$p = (isset($_POST['phone'])) ? $_POST['phone'] : '';
			setcookie('cart_courier_phone',$p,time()+3600,'/');
		}

		public function delivery_payment_match_check(){
			if(isset($_POST['order_vals']['delivery']) && isset($_POST['order_vals']['payment']) && isset($this->delivery_payment_match[$_POST['order_vals']['delivery']]) && in_array($_POST['order_vals']['payment'],$this->delivery_payment_match[$_POST['order_vals']['delivery']])){
				return true;
			}else{
				return false;
			}
		}

	}
?>