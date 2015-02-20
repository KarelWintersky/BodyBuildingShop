<?
	Class f_Profile{

		private $registry;

		public function pgc(){}

		public function __construct($registry){
			$this->registry = $registry;
			$this->registry->set('f_profile',$this);
		}

		public function path_check(){

			if(!isset($_SESSION['user_id'])){header('Location: /auth/');}

			$this->registry['f_404'] = false;
			$path_arr = $this->registry['route_path'];

			$this->registry['template']->add2crumbs('profile','Личный кабинет');
			$this->registry['noindex'] = true;
			$this->registry['register_page'] = 1; //для того, чтобы в меню пункт "Профиль" был активным, костыль с пункта "Регистрация"

			if(count($path_arr)==0){
				$this->registry['template']->set('c','profile/settings');
				$this->registry['longtitle'] = 'Ваши установки';
				$this->get_all_user_info();
				
				$this->registry['CL_css']->set(array(
						'profile',
				));				
				
				return true;
			}elseif(count($path_arr)==1 && $path_arr[0]=='orders'){
				$this->registry['template']->set('c','profile/orders');
				$this->registry['longtitle'] = 'Ваши заказы';
				$this->get_all_user_info();
				
				$this->registry['CL_css']->set(array(
						'profile',
				));				
				
				return true;
			}elseif(count($path_arr)==1 && $path_arr[0]=='returnaccount'){
				$this->registry['template']->set('c','profile/returnaccount');
				$this->registry['longtitle'] = 'Забрать деньги';
				$this->get_all_user_info();
				
				$this->registry['CL_css']->set(array(
						'profile',
				));				
				
				return true;
			}elseif(count($path_arr)==1 && $path_arr[0]=='accountorder'){
				$this->registry['template']->set('c','profile/accountorder');
				$this->registry['longtitle'] = 'Пополнить личный счет';
				$this->get_all_user_info();
				$this->mk_account_order();
				
				$this->registry['CL_css']->set(array(
						'profile',
				));				
				
				return true;
			}elseif(count($path_arr)==2 && $path_arr[0]=='orders' && $this->order_check($path_arr[1])){
				$this->registry['template']->set('c','profile/order');
				$this->get_all_user_info();
				
				$this->registry['CL_css']->set(array(
						'profile',
				));				
				
				return true;
			}elseif(count($path_arr)==3 && $path_arr[0]=='orders' && $this->order_check($path_arr[1]) && $path_arr[2]=='pay' && $this->pay_check($path_arr[1])){
				$this->registry['template']->set('c','profile/order_pay');
				$this->get_all_user_info();
				$this->mk_roboxchange_data($path_arr[1]);
				
				$this->registry['CL_css']->set(array(
						'profile',
				));				
				
				return true;
			}

			$this->registry['f_404'] = true;
			return false;
		}

		private function mk_account_order(){
			if(count($_POST)>0 && isset($_POST['sum'])){
				$sum = $_POST['sum'];
				$user_id = $this->registry['userdata']['id'];

				$qLnk = mysql_query("SELECT (IFNULL(MAX(account_orders.user_num),0)+1) FROM account_orders WHERE account_orders.user_id = '".$user_id."';");
				$user_num = mysql_result($qLnk,0);

				mysql_query("
							INSERT INTO
								account_orders
								(account_orders.user_id,
									account_orders.user_num,
										account_orders.createdon,
											account_orders.sum,
												account_orders.status)
								VALUES
								('".$user_id."',
									'".$user_num."',
										NOW(),
											'".$sum."',
												1);
							");
				$order_id = mysql_insert_id();
				$order_num = $order_id.'/'.$user_num.'/А';

				$output = array(
					'num' => $order_num,
					'sum' => $sum
				);

				$this->registry['new_account_order_data'] = $output;

				$this->registry['logic']->send_account_order($order_id);

				$this->send_to_admins($order_num);

			}
		}

		private function send_to_admins($order_num){

			$emails = explode('::',ADMINS_EMAILS);
			if(count($emails)>0){
				foreach($emails as $admin_mail){
					$replace_arr = array(
						'ORDER_NUM' => $order_num
					);
					$mailer = new Mailer($this->registry,24,$replace_arr,$admin_mail);
				}
			}

		}

		private function mk_roboxchange_data($num){

			$id_arr = explode('-',$num);
			$order_id = implode('/',$id_arr);

			$login = ROBOKASSA_LG; //$mrh_login
			$pwd = ROBOKASSA_PW; //$mrh_pass1
			$unique_id = $this->registry['orderdata']['ai'];; //$inv_id
			$desc = 'Оплата заказа № '.$order_id.' в Бодибилдинг-Магазине'; //$inv_desc
			$sum = $this->registry['orderdata']['overall_price']; //$out_summ
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

		private function pay_check($num){
			$id_arr = explode('-',$num);
			$qLnk = mysql_query("
								SELECT
									COUNT(*)
								FROM
									orders
								WHERE
									orders.user_id = '".$this->registry['userdata']['id']."'
									AND
									orders.id = '".$id_arr[0]."'
									AND
									orders.user_num	= '".$id_arr[1]."'
									AND
									orders.payment_method = '".$id_arr[2]."'
									AND
									orders.by_card = 1
									AND
									orders.status = 1
			");

			return (mysql_result($qLnk,0)>0) ? true : false;

		}

		private function order_check($num){

			$id_arr = explode('-',$num);

			$qLnk = mysql_query("
								SELECT
									orders.*
								FROM
									orders
								WHERE
									orders.id = '".$id_arr[0]."'
									AND
									orders.user_num = '".$id_arr[1]."'
									AND
									orders.payment_method = '".$id_arr[2]."'
									AND
									orders.user_id = '".$this->registry['userdata']['id']."'
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$orderdata = mysql_fetch_assoc($qLnk);
				$orderdata['num'] = $orderdata['id'].'/'.$orderdata['user_num'].'/'.$orderdata['payment_method'];

				if($orderdata['delivery_type']==1){
					$orderdata['delivery_type_text'] = 'по почте';
				}elseif($orderdata['delivery_type']==2){
					$orderdata['delivery_type_text'] = 'курьером';
				}elseif($orderdata['delivery_type']==3){
					$orderdata['delivery_type_text'] = 'транспортной компанией';
				}

				if($orderdata['payment_method']=='Н' || $orderdata['payment_method']=='H'){
					$orderdata['payment_type_text'] = 'наложенным платежом';
				}elseif($orderdata['payment_method']=='W'){
					$orderdata['payment_type_text'] = 'электронными деньгами';
				}elseif($orderdata['payment_method']=='П'){
					if($orderdata['by_card']==1){
						$orderdata['payment_type_text'] = 'банковской картой или через платежные системы';
					}elseif($orderdata['pay2courier']==1){
						$orderdata['payment_type_text'] = 'курьеру';
					}elseif($orderdata['from_account']>0){
						$orderdata['payment_type_text'] = 'с личного счета';
					}else{
						$orderdata['payment_type_text'] = 'предоплата';
					}

				}



				$this->registry['orderdata'] = $orderdata;

				$this->registry['longtitle'] = 'Заказ номер '.$orderdata['num'];
				return true;
			}else{
				return false;
			}
		}

		private function get_all_user_info(){

			$qLnk = mysql_query("SELECT users.* FROM users WHERE users.id = '".$_SESSION['user_id']."';");
			$this->registry['full_ui'] = (isset($_COOKIE['profile_data'])) ? (array)json_decode($_COOKIE['profile_data']) : mysql_fetch_assoc($qLnk);
		}

		public function item_rq($name,$a = NULL){
			require($this->registry['template']->TF.'item/profile/'.$name.'.html');
		}

		public function sav_profile(){
			$err_array = array();

			if($_POST['pass']!=$_POST['pass_confirm']){
				$err_array[] = 'Пароли не совпадают';
			}

			if($_POST['name']==''){$err_array[] = 'Укажите фио';	}

			if($_POST['email']==''){
				$err_array[] = 'Укажите email';
			}elseif(!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)){
				$err_array[] = 'Укажите корректный email';
			}

			if($_POST['city']==''){$err_array[] = 'Укажите город';}

			if($_POST['street']==''){$err_array[] = 'Укажите улицу';}

			if($_POST['house']==''){$err_array[] = 'Укажите номер дома';}

			if(count($err_array)>0){
				$cookie_timer = time()+1800;
			}else{

				$this->mk_sav();
				$this->admins_notify();

				$cookie_timer = time()-1800;
			}

			setcookie('err_array',json_encode($err_array),$cookie_timer,'/');
			setcookie('profile_data',json_encode($_POST),$cookie_timer,'/');
		}

		private function admins_notify(){
			$replace_arr = array(
				'LOGIN' => $_POST['login'],
				'ID' => $_SESSION['user_id'],
				'OLD_FIO' => $_POST['old_name'],
				'OLD_EMAIL' => $_POST['old_email'],
				'OLD_INDEX' => $_POST['old_zip_code'],
				'OLD_COUNTRY' => 'Россия',
				'OLD_REGION' => $_POST['old_region'],
				'OLD_CITY' => $_POST['old_city'],
				'OLD_STREET' => $_POST['old_street'],
				'OLD_HOUSE' => $_POST['old_house'],
				'OLD_CORPUS' => $_POST['old_corpus'],
				'OLD_FLAT' => $_POST['old_flat'],
				'OLD_WISHES' => $_POST['old_wishes'],
				'PASS' => $_POST['pass'],
				'NEW_FIO' => $_POST['name'],
				'NEW_EMAIL' => $_POST['email'],
				'NEW_INDEX' => $_POST['zip_code'],
				'NEW_COUNTRY' => 'Россия',
				'NEW_REGION' => $_POST['region'],
				'NEW_CITY' => $_POST['city'],
				'NEW_STREET' => $_POST['street'],
				'NEW_HOUSE' => $_POST['house'],
				'NEW_CORPUS' => $_POST['corpus'],
				'NEW_FLAT' => $_POST['flat'],
				'NEW_WISHES' => $_POST['wishes'],
			);

			foreach($replace_arr as $key => $val){$replace_arr[$key] = iconv('utf-8','windows-1251',$val);}

			$emails = explode('::',ADMINS_EMAILS);
			if(count($emails)>0){
				foreach($emails as $admin_mail){
					$mailer = new Mailer($this->registry,28,$replace_arr,$admin_mail,false,'windows-1251');
				}
			}

		}

		private function mk_sav(){

			$q_pass = (isset($_POST['pass']) && $_POST['pass']!='') ? "users.pass = '".md5($_POST['pass'])."'," : "";

			$get_news = (isset($_POST['get_news_val']) && $_POST['get_news_val']==1) ? 1 : 0;
			$get_catalog_changes = (isset($_POST['get_catalog_changes_val']) && $_POST['get_catalog_changes_val']==1) ? 1 : 0;

			mysql_query("
						UPDATE
							users
						SET
							users.name = '".$_POST['name']."',
							".$q_pass."
							users.phone = '".$_POST['phone']."',
							users.email = '".$_POST['email']."',
							users.zip_code = '".trim($_POST['zip_code'])."',
							users.region = '".$_POST['region']."',
							users.district = '".$_POST['district']."',
							users.city = '".$_POST['city']."',
							users.street = '".$_POST['street']."',
							users.house = '".$_POST['house']."',
							users.corpus = '".$_POST['corpus']."',
							users.flat = '".$_POST['flat']."',
							users.wishes = '".$_POST['wishes']."',
							users.get_news = '".$get_news."',
							users.get_catalog_changes = '".$get_catalog_changes."'
						WHERE
							users.id = '".$_SESSION['user_id']."'
						");
		}

		public function prof_err(){
			if(isset($_COOKIE['err_array'])){
				$html = '';
				foreach(json_decode($_COOKIE['err_array']) as $error){
					$html.= '<li>'.$error.'</li>';
				}
				echo '<ol id="profile_upper_err">'.$html.'</ol>';
			}
		}

		public function get_your_orders(&$orders){

			$orders = array();

			$qLnk = mysql_query("
								SELECT
									orders.*
								FROM
									orders
								WHERE
									orders.user_id = '".$this->registry['full_ui']['id']."'
								ORDER BY
									orders.id DESC;
								");
			while($o = mysql_fetch_assoc($qLnk)){
				$orders[$o['status']][] = $o;
			}
						
		}

		public function print_orders($orders,$type,$colspan){

			$types = (is_array($type)) ? $type : array($type);
			
			$output = array();
			
			foreach($types as $type){
				if(isset($orders[$type])){
					foreach($orders[$type] as $l){
						
						$l['num'] = $l['id'].'/'.$l['user_num'].'/'.$l['payment_method'];
						$l['lnk'] = $l['id'].'-'.$l['user_num'].'-'.$l['payment_method'];

						$output[$l['ai']] = $l;
					}
				}else{
					echo '<td class="no_orders" colspan="'.$colspan.'">Нет ни одного заказа</td>';
				}
			}

			ksort($output,SORT_NUMERIC);
			$output=array_reverse($output);
						
			foreach($output as $l){
				$this->item_rq('order_tr',$l);
			}
						
		}

		public function order_goods_table(&$is_barcodes,&$final_price){
			$final_price = 0;
			$goods = array();
			$barcodes = array();
			$qLnk = mysql_query("
								SELECT
									orders_goods.*
								FROM
									orders_goods
								WHERE
									orders_goods.order_id = '".$this->registry['orderdata']['num']."'
								ORDER BY
									orders_goods.final_price DESC;
								");
			while($g = mysql_fetch_assoc($qLnk)){
				$goods[] = $g;
				$barcodes[] = $g['goods_barcode'];
				
				$final_price+=$g['price']*$g['amount'];
			} 
						
			$is_barcodes = false;
			foreach($goods as $g) if($g['goods_id']==0 && $g['price']>0) $is_barcodes = true;
			
			if($is_barcodes){
				
				foreach($goods as $key => $g){
					if($g['goods_feats_str']!='' && is_numeric($g['goods_feats_str'])){
						$goods[$key]['goods_feats_str'] = mysql_result(mysql_query(sprintf("SELECT IFNULL(name,'') FROM features WHERE id = '%d'",$g['goods_feats_str'])),0);
					}
				}			
				
				$qLnk = mysql_query(sprintf("
						SELECT
							goods_barcodes.barcode,
							goods_barcodes.feature,
							goods.alias,
							levels.alias AS level_alias,
							parent_tbl.alias AS parent_alias,
							parent_tbl.id AS parent_parent_id						
						FROM
							goods_barcodes
						INNER JOIN goods ON goods.id = goods_barcodes.goods_id 
						LEFT OUTER JOIN levels ON levels.id = goods.level_id
						LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
						WHERE
							goods_barcodes.barcode IN (%s)
						",
						implode(",",$barcodes)
				));		
				
			while($g = mysql_fetch_assoc($qLnk)){
				foreach($goods as $key => $gitem){
					if($gitem['goods_barcode']==$g['barcode']){
						$goods[$key]['alias'] = $g['alias'];
						$goods[$key]['level_alias'] = $g['level_alias'];
						$goods[$key]['parent_alias'] = $g['parent_alias'];						
						$goods[$key]['feature'] = $g['feature'];						
						$goods[$key]['parent_parent_id'] = $g['parent_parent_id'];						
					}
				}
			}		
				
			}else{
				
				$newgoods = array();
				foreach($goods as $key => $val){
					$newgoods[$val['goods_id']] = $val; 
				}
				$goods = $newgoods;
				
				$qLnk = mysql_query(sprintf("
						SELECT
							goods.id,
							goods.alias,
							levels.alias AS level_alias,
							parent_tbl.alias AS parent_alias
						FROM
							goods
						LEFT OUTER JOIN levels ON levels.id = goods.level_id
						LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
						WHERE
							goods.id IN (%s)
						",
						implode(",",array_keys($goods))
						));		
				
				while($g = mysql_fetch_assoc($qLnk)){
					$goods[$g['id']]['alias'] = $g['alias'];
					$goods[$g['id']]['level_alias'] = $g['level_alias'];
					$goods[$g['id']]['parent_alias'] = $g['parent_alias'];
				}		
			}
						
			foreach($goods as $g){
				$this->item_rq('order_goods_tr',$g);
			}
		}

		public function get_cart_sum(){
			$cart = 0;
			if(isset($_COOKIE['thecart']) && $_COOKIE['thecart']!=''){

				$ids = array();

				$array = explode('||',$_COOKIE['thecart']);
				foreach($array as $str){
					$a = explode(':',$str);
					$ids[] = $a[0];
				}

				$ids = array_count_values($ids);
				
				$qLnk = mysql_query(sprintf("
									SELECT
										goods_barcodes.barcode,
										goods_barcodes.price - goods_barcodes.price*%d/100 AS price,
										goods.personal_discount
									FROM
										goods_barcodes
									INNER JOIN goods ON goods.id = goods_barcodes.goods_id
									WHERE
										goods_barcodes.barcode IN (%s);
									",OVERALL_DISCOUNT,implode(',',array_keys($ids))));
				while($g = mysql_fetch_assoc($qLnk)){
					$cart+= ($g['price'] - intval($g['price']*($g['personal_discount']+$this->registry['userdata']['personal_discount'])/100)*$ids[$g['barcode']]);
				}

			}
			return Common_Useful::price2read($cart);
		}

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
		}

	}
?>