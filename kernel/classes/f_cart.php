<?
	Class f_Cart{

		private $registry;
		public $payment_types;
		public $delivery_types;

		public function pgc(){}

		public function __construct($registry){
			$this->registry = $registry;
			$this->registry->set('f_cart',$this);

			$this->payment_types = array(
				1 => array(
					'name' => 'Заказать наложенным платежом',
					'active' => '0',
					'disabled' => '0',
					'var' => 'pay_nalog'	
				),
				2 => array(
					'name' => 'Получить счет на предоплату через банк',
					'active' => '0',
					'disabled' => '0',
					'var' => 'pay_bill'
				),
				3 => array(
					'name' => 'Оплата через WebMoney, Яндекс-деньги',
					'active' => '0',
					'disabled' => '0',
					'var' => 'pay_webmoney'
				),
				4 => array(
					'name' => 'Оплата банковской картой',
					'active' => '0',
					'disabled' => '0',
					'var' => 'pay_card'
				),
				5 => array(
					'name' => 'Наличными курьеру или в магазине',
					'active' => '0',
					'disabled' => '0',
					'var' => 'pay_nal'
				),
				6 => array(
					'name' => 'Оплата c лицевого счета в нашем магазине',
					'add_txt' => '(<span><a href="/profile/accountorder/">пополнить счет</a></span>)',
					'active' => '0',
					'disabled' => '0',
					'var' => 'pay_account'
				),
				7 => array(
					'name' => 'Другие платежные системы',
					'add_txt' => '(QIWI, RBK Money, моб. телефон, Альфа-банк...)',
					'active' => '0',
					'disabled' => '0',
					'var' => 'pay_other'
				)
			);

			$this->delivery_types = array(
				1 => array(
					'name' => 'Доставка по почте',
					'active' => '0',
					'disabled' => '0',
					'var' => 'delivery_mail'
				),
				2 => array(
					'name' => 'Доставка курьером',
					'active' => '0',
					'disabled' => '0',
					'var' => 'delivery_courier'
				),
				/*3 => array(
					'name' => 'Доставка транспортной компанией',
					'active' => '0',
					'disabled' => '0',
				),*/
				4 => array(
					'name' => 'Самовывоз',
					'active' => '0',
					'disabled' => '0',
					'var' => 'delivery_self'
				),
			);

			$this->delivery_payment_match = array(
				1 => array(1,2,3,4,6,7),
				2 => array(2,3,4,5,6,7),
				3 => array(2,3,4,6,7),
				4 => array(2,3,4,5,6,7)
			);
			
			$this->store_params();
		}

		private function store_params(){
			$params = array();
			$qLnk = mysql_query("SELECT name, value FROM dp_params");
			while($p = mysql_fetch_assoc($qLnk)) $params[$p['name']] = $p['value'];
			
			$this->registry['dp_params'] = $params;
		}
		
		public function path_check(){

			if(isset($_SESSION['user_id'])){

				$this->registry['f_404'] = false;
				$path_arr = $this->registry['route_path'];

				$this->registry['template']->add2crumbs('cart','Корзина');
				$this->registry['noindex'] = true;

				if(count($path_arr)==0){
					$this->registry['template']->set('c','cart/main');
					$this->registry['longtitle'] = 'Корзина';
					return true;
				}elseif(count($path_arr)==1 && $path_arr[0]=='order'){
					if(!isset($_COOKIE['thecart']) || $_COOKIE['thecart']==''){header('Location: /cart/');exit();}
					$this->registry['template']->add2crumbs('order','Оформление заказа');
					$this->registry['template']->set('c','cart/order');
					$this->registry['longtitle'] = 'Оформление заказа';
					return true;
				}elseif(count($path_arr)==2 && $path_arr[0]=='order' && $path_arr[1]=='check'){
					if(!isset($_COOKIE['thecart']) || $_COOKIE['thecart']==''){header('Location: /cart/');exit();}
					$this->registry['template']->add2crumbs('order','Оформление заказа');
					$this->registry['template']->set('c','cart/check');
					$this->registry['longtitle'] = 'Проверка заказа';
					$this->check_if_spb();
					$this->wishesphone_2_cookie();
					$this->coupon_apply();
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

		public function item_rq($name,$a = NULL){
			require($this->registry['template']->TF.'blocks/item/cart/'.$name.'.html');
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

		public function print_table_line_feats($feats_arr){
			$fa = array();
			foreach($feats_arr as $group_id => $feat_id){
				$fa[] = $this->registry['full_cart_arr']['feats'][$group_id]['name'].': '.$this->registry['full_cart_arr']['feats'][$group_id]['feats'][$feat_id];
			}
			if(count($fa)>0){
				echo implode(', ',$fa);
			}
		}

		public function long_cart_construct($this_page_url,$cart_courier_phone){
			$goods_ids = array();
			if(isset($_COOKIE['thecart']) && $_COOKIE['thecart']!=''){
				$cart_arr = explode('|',$_COOKIE['thecart']);
				
				if(count($cart_arr)>0){

					$this->registry['cart_courier_phone'] = $cart_courier_phone;

					$cart_arr = $this->mk_cart_array($cart_arr);
					$this->registry['full_cart_arr'] = $this->mk_full_array($cart_arr);

					$this->registry['overall_weight'] = $this->registry['full_cart_arr']['overall_weight'];
					
					ob_start();
					$i = 1;
					$overall_price = 0;
					foreach($this->registry['full_cart_arr']['goods'] as $cart_line){
						$cart_line['index'] = $i;
						$cart_line['cookie_string'] = sprintf('%s:%s:%d',$cart_line['barcode'],$cart_line['packing'],$cart_line['amount']);
						
						
						if($cart_line['color']!==false) $cart_line['cookie_string'] = sprintf('%s:%s',$cart_line['cookie_string'],$cart_line['color']);
						
						$cart_line['feature_color_string'] = $this->feature_color_string($cart_line,$this->registry['full_cart_arr']['goods_arr']);
						
						$cart_line['old_price'] = $cart_line['price'];
						$cart_line['price'] = $cart_line['price']-$cart_line['price']*$cart_line['personal_discount']/100; 
						
						$this->item_rq('table_line',$cart_line);

						$overall_price+=$cart_line['price']*$cart_line['amount'];
						$i++;
					}
					$a['lines'] = ob_get_contents();
					ob_end_clean();

					$full_cart_arr = $this->registry['full_cart_arr'];
					$full_cart_arr['overall_price'] = $overall_price;
					$this->registry['full_cart_arr'] = $full_cart_arr; 
					
					$this->item_rq('cart_ostatok_hint',NULL);

					ob_start();
					$this->item_rq('table_body',$a);
					$html = ob_get_contents();
					ob_end_clean();

					ob_start();

					$this->item_rq('cart_sum',$a);
					if(count($this_page_url)==1 && $this_page_url[0]=='cart'){
						$this->item_rq('cart_go');
					}else{
						$this->item_rq('cart_hints');
						
						$this->delivery_count();

						$this->item_rq('cart_delivery');
						$this->item_rq('cart_delivery_payment');
						
						$this->item_rq('cart_gift');
					}
					$html.= ob_get_contents();
					ob_end_clean();

				}
			}else{
				ob_start();
				$this->item_rq('cart_empty');
				$html.= ob_get_contents();
				ob_end_clean();

			}

			echo $html;
		}

		private function delivery_count(){

			$index_arr = $this->registry['logic']->get_index_data(trim($this->registry['userdata']['zip_code']));

			if($this->registry['delivery_type']==1){

				$weight = $this->registry['overall_weight'];
				/*$boxtype = ($weight<BANDEROL_MAX_WEIGHT && $this->registry['full_cart_arr']['overall_price']<BANDEROL_MAX_PRICE) ? 0 : 1; //0 - banderol, 1 - posylka
				$boxtype_name = ($boxtype==0) ? 'бандероли'	: 'посылки';	*/
				$post_500s = ceil($weight/500);

				if($index_arr){

					$main_cost = $index_arr['tarif_pos_basic'];
					$add_cost_ind = $index_arr['tarif_pos_add'];
						$add_cost = $add_cost_ind*($post_500s-1);

					$total_cost = $main_cost + $add_cost;

					//hard region

					$hard_cost_index = $index_arr['tarif_post_avia_pos'] + $index_arr['tarif_avia_pos'];
						$hard_cost = $hard_cost_index*$post_500s;

					$this->registry['delivery_cost_array'] = array(
						'total_cost' => $total_cost,
						'hard_cost' => $hard_cost,
						'cost' => $hard_cost+$total_cost,
						'is_spb' => $index_arr['is_spb']
					);

					if($hard_cost>0){
						$this->registry['no_nalog'] = true;
					}

				}else{
					$this->registry['false_index'] = true;
					$this->registry['no_nalog'] = true;

					$this->registry['delivery_cost_array'] = array(
						'total_cost' => 0,
						'hard_cost' => 0,
						'cost' => 0,
						'is_spb' => 0,
					);
				}

			}elseif($this->registry['delivery_type']==2){
				$this->registry['delivery_cost_array'] = array(
					'cost' => ($index_arr['is_spb']==0 || $this->registry['full_cart_arr']['overall_price']>=FREE_DELIVERY_SUM) ? 0 : COURIER_SPB_COST,
					'is_spb' => $index_arr['is_spb'],
				);
			}else{
				$this->registry['delivery_cost_array'] = array(
					'cost' => 0,
					'is_spb' => $index_arr['is_spb'],
				);
			}

		}

		private function mk_full_array($cart_arr){

			$this->registry['delivery_type'] = (isset($_COOKIE['delivery_type'])) ? $_COOKIE['delivery_type'] : 1;
			$this->registry['payment_type'] = (isset($_COOKIE['payment_type'])) ? $_COOKIE['payment_type'] : 2;

			$goods_arr = array();
			$goods_ids = array();
			foreach($cart_arr as $key => $g){
				
				if($g['color']!==false){
					$qLnk = mysql_query(sprintf("
							SELECT name FROM features WHERE id = '%d' 
							",$g['color']));
					
					$cart_arr[$key]['color_name'] = mysql_result($qLnk,0);
				}
				
				$qLnk = mysql_query(sprintf("
					SELECT
						*
					FROM
						goods_barcodes
					WHERE
						barcode = '%s'
						AND
						packing = '%s'
					",
					$g['barcode'],
					$g['packing']
					));
				$goods = mysql_fetch_assoc($qLnk);

				$cart_arr[$key]['goods_id'] = $goods['goods_id'];
				$cart_arr[$key]['price'] = $goods['price'];
				$cart_arr[$key]['feature'] = $goods['feature'];
				$cart_arr[$key]['weight'] = $goods['weight'];
				
				$goods_ids[] = $goods['goods_id'];
			}
			
			/*
			(goods.personal_discount + ".OVERALL_DISCOUNT.") AS personal_discount,
			goods.price_1,
			goods.price_2,
			(goods.price_1 - goods.price_1*(goods.personal_discount + ".OVERALL_DISCOUNT.")/100) AS price_1_final,
			(goods.price_1 - goods.price_1*".OVERALL_DISCOUNT."/100) AS price_1_final_wout_disc,
			(goods.price_2 - goods.price_2*(goods.personal_discount + ".OVERALL_DISCOUNT.")/100) AS price_2_final,
			(goods.price_2 - goods.price_2*".OVERALL_DISCOUNT."/100) AS price_2_final_wout_disc,
			*/

			$qLnk = mysql_query("
								SELECT
									goods.id,
									goods.name,
									goods.alias,
									goods.grower_id,
									goods.personal_discount,
									goods.delivery_way_id,
									levels.alias AS level_alias,
									parent_tbl.alias AS parent_alias,
									parent_tbl.id AS root_id,
									growers.name AS grower,
									ostatki.value AS ostatok_value
								FROM
									goods
								LEFT OUTER JOIN growers ON growers.id = goods.grower_id
								LEFT OUTER JOIN levels ON levels.id = goods.level_id
								LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
								LEFT OUTER JOIN ostatki ON ostatki.goods_id = goods.id
								WHERE
									goods.id IN (".implode(",",$goods_ids).")
									AND
									goods.published = 1
								ORDER BY
									goods.id ASC;
								");
			while($g = mysql_fetch_assoc($qLnk)){
				if($g['delivery_way_id']==1){$this->registry['delivery_only_company']=true;}
				$goods_arr[$g['id']] = $g;

				if(!is_null($g['ostatok_value'])){
					$this->registry['no_nalog'] = true;
				}

			}

			foreach($cart_arr as $key => $g){
				$cart_arr[$key]['name'] = $goods_arr[$g['goods_id']]['name'];
				$cart_arr[$key]['alias'] = $goods_arr[$g['goods_id']]['alias'];
				$cart_arr[$key]['grower_id'] = $goods_arr[$g['goods_id']]['grower_id'];
				$cart_arr[$key]['delivery_way_id'] = $goods_arr[$g['goods_id']]['delivery_way_id'];
				$cart_arr[$key]['level_alias'] = $goods_arr[$g['goods_id']]['level_alias'];
				$cart_arr[$key]['parent_alias'] = $goods_arr[$g['goods_id']]['parent_alias'];
				$cart_arr[$key]['personal_discount'] = $goods_arr[$g['goods_id']]['personal_discount'];
				$cart_arr[$key]['grower'] = $goods_arr[$g['goods_id']]['grower'];
				$cart_arr[$key]['ostatok_value'] = $goods_arr[$g['goods_id']]['ostatok_value'];
			}

			/*$qLnk = mysql_query("
								SELECT
									features.id,
									features.name,
									features.group_id,
									feature_groups.name AS group_name
								FROM
									features
								INNER JOIN feature_groups ON feature_groups.id = features.group_id
								WHERE
									features.id IN (".implode(",",$feats_ids).")
								ORDER BY
									features.group_id ASC,
									features.id ASC;
								");
			while($f = mysql_fetch_assoc($qLnk)){
				$feats_arr[$f['group_id']]['name'] = $f['group_name'];
				$feats_arr[$f['group_id']]['feats'][$f['id']] = $f['name'];
			}*/

			/*foreach($cart_arr as $arr){

				$feats = array();
				$feats_4_cookiestring = array();

				foreach($arr['feats'] as $feat_id){
					foreach($feats_arr as $group_id => $fts){
						if(in_array($feat_id,array_keys($fts['feats']))){
							$feats[$group_id] = $feat_id;
						}
					}
				}

				$cookie_feats = (count($feats)>0) ? implode(',',$feats) : '0';
				$cookie_string = $arr['id'].'|'.$cookie_feats;

				$full_array[] = array(
					'amount' => $arr['amount'],
					'goods_info' => $goods_arr[$arr['id']],
					'feats' => $feats,
					'cookie_string' => $cookie_string
				);
			}*/

			$ostatki = array();
			$qLnk = mysql_query("
								SELECT
									ostatki.*
								FROM
									ostatki
								WHERE
									ostatki.goods_id IN (".implode(",",array_keys($goods_arr)).");
								");
			while($o = mysql_fetch_assoc($qLnk)){
				$ostatki[] = $goods_arr[$o['goods_id']]['name'];
			}
			if(count($ostatki)>0){
				$this->registry->set('ostatki_data',$ostatki);
			}


			$A['goods_arr'] = $goods_arr;
			$A['goods'] = $cart_arr;
			//$A['feats'] = $feats_arr;
			$A['overall_price'] = $this->mk_overall_price($cart_arr);
			$A['overall_weight'] = $this->mk_overall_weight($cart_arr);

			return $A;

		}

		private function mk_overall_weight($full_array){
			$weight = 0;
			foreach($full_array as $item){
				$goods_weight = intval($item['weight']);
				$weight+= $item['amount']*$goods_weight;
			}

			return $weight;
		}

		private function mk_overall_price($full_array){
			$price = 0;
			foreach($full_array as $item){
				$goods_price = intval($item['price']);
				$price+= $item['amount']*$goods_price;
			}

			if(($price + $price*PREPAY_DISCOUNT/100)>$this->registry['userdata']['max_nalog']){
				$this->registry['no_nalog'] = true;
			}

			return $price;

		}

		private function mk_cart_array($cart_arr){
			$new_arr = array();

			foreach($cart_arr as $goods_string){

				$goods_arr = explode(':',$goods_string);
				$barcode = $goods_arr[0];
				$packing = $goods_arr[1];
				$amount = $goods_arr[2];
				$color = (isset($goods_arr[3])) ? $goods_arr[3] : false;

				$new_arr[] = array(
					'amount' => $amount,
					'barcode' => $barcode,
					'packing' => $packing,
					'color' => $color
				);
			}

			return $new_arr;

		}

		public function gifts_list(&$active_gift_id){

			$active_gift_barcode = 0;
			$gift_weight = 0;

			if(isset($_COOKIE['cart_gift_id']) && $_COOKIE['cart_gift_id']!='' && $_COOKIE['cart_gift_id']!=0){
				$active_gift_barcode = $_COOKIE['cart_gift_id'];
			}

			$gift_max_price = $this->registry['full_cart_arr']['overall_price']*GIFT_PERCENT/100;

			$gifts_arr = array();

			$qLnk = mysql_query(sprintf("
					SELECT
						goods.name,
						goods_barcodes.barcode,					
						goods_barcodes.packing,					
						goods_barcodes.feature,
						goods_barcodes.weight
					FROM
						goods_barcodes
					INNER JOIN goods ON goods.id = goods_barcodes.goods_id 
					WHERE	
						goods.published = 1
						AND
						goods_barcodes.weight > 0
						AND
						goods_barcodes.present = 1
						AND
						goods_barcodes.price <= '%s'
					ORDER BY
						goods.level_id ASC,
						goods.name ASC					
					",$gift_max_price));
			echo mysql_error();
			while($g = mysql_fetch_assoc($qLnk)){
				$name = array(
						$g['name'],
						$g['packing'],
						$g['feature'],
						);
				foreach($name as $key => $val) if($val=='') unset($name[$key]);
				
				$gifts_arr[$g['barcode']]['name'] = implode(", ",$name);
				
				
				$gifts_arr[$g['barcode']]['active'] = ($active_gift_barcode==$g['barcode']) ? 1 : 0;
				$gift_weight = ($active_gift_barcode==$g['barcode']) ? intval($g['weight']) : $gift_weight;
			}


			$none_active = ($active_gift_barcode==0) ? 1 : 0;
			$gifts_arr[0] = array('name' => 'Подарок на усмотрение администрации','active' => $none_active);

			$this->registry['template']->F_dropdown($gifts_arr,'gift','','add_gift_2_cart(this);');

			$this->registry['overall_weight'] = $this->registry['full_cart_arr']['overall_weight'] + $gift_weight;

		}

		public function print_delivery_types(){

			$dt = $this->registry['delivery_type'];
			$this->delivery_types[$dt]['active'] = 1;

			/*if(isset($this->registry['delivery_only_company']) && $this->registry['delivery_only_company']){
				$this->delivery_types[1]['disabled'] = 1;
				$this->delivery_types[2]['disabled'] = 1;
				$this->delivery_types[3]['active'] = 1;
			}elseif($this->registry['full_cart_arr']['overall_price']<MIN_TRANSPORT_COMPANY){
				$this->delivery_types[3]['disabled'] = 1;
			}*/

			//$this->delivery_types[2]['disabled'] = 1;

			if(isset($this->registry['false_index'])){
				$this->delivery_types[1]['disabled'] = 1;
				$this->delivery_types[2]['active'] = 1;
			}

			/*if($this->registry['delivery_cost_array']['is_spb']==0){
				$this->delivery_types[4]['disabled'] = 1;
				$this->delivery_types[1]['active'] = 1;
			}*/
			
			foreach($this->delivery_types as $id => $arr){
				$a['id'] = $id;
				$a['arr'] = $arr;
				$a['type'] = 'delivery';
				$a['onchange'] = 'delivery_type_change(this);';
				$a['class'] = 'delivery_radio';
				$a['var'] = $this->registry['dp_params'][$arr['var']];
				$this->item_rq('radio_line',$a);
			}
		}
		
		public function print_payment_types_add(){
			$ptypes = $this->payment_types;
			unset($ptypes[1]);unset($ptypes[6]);

			$ptypes[2]['active'] = 1;

			if($this->registry['is_spb']==0){
				$ptypes[5]['disabled'] = 1;
			}

			foreach($ptypes as $id => $arr){
				$a['id'] = $id;
				$a['arr'] = $arr;
				$a['type'] = 'payment_method';
				$a['onchange'] = '';
				$a['class'] = '';
				//$a['var'] = $this->registry['dp_params'][$arr['var']];
				$this->item_rq('radio_line',$a);
			}

			$this->registry['unset_prev_payment_method'] = true;
		}

		public function print_payment_types(){

			$pt = $this->registry['payment_type'];
			if($pt==1 && isset($this->registry['no_nalog']) && $this->registry['no_nalog']){
				$this->payment_types[2]['active'] = 1;
			}else{
				$this->payment_types[$pt]['active'] = 1;
			}

			if((isset($this->registry['no_nalog']) && $this->registry['no_nalog'])){
				$this->payment_types[1]['disabled'] = 1;
			}

			if($this->registry['delivery_cost_array']['is_spb']==0){
				//$this->payment_types[5]['disabled'] = 1;
			}

			if($this->registry['userdata']['my_account']==0){
				$this->payment_types[6]['disabled'] = 1;
			}

			/*if($this->registry['userdata']['id']!=32099 && $this->registry['userdata']['id']!=93 && isset($this->payment_types[4])){
				unset($this->payment_types[4]);
			}*/

			foreach($this->payment_types as $id => $arr){
				$a['id'] = $id;
				$a['arr'] = $arr;
				$a['type'] = 'payment';
				$a['onchange'] = 'payment_type_change(this);';
				$a['class'] = 'payment_radio';
				$a['var'] = $this->registry['dp_params'][$arr['var']];
				$this->item_rq('radio_line',$a);
			}
		}

		public function check_goods_table(&$total_goods_price){

			$ptype = $_POST['order_vals']['payment'];
			$goods_table = $_POST['order_vals']['goods_table'];
			$goods_ids = array();
			$goods_data = array();
			$total_goods_price = 0;
						
			foreach($goods_table as $key => $line){
				/*if((isset($line['present']) && $line['present']==1) || $line['is_gift']==1){
					$goods_ids[] = $line['goods_id'];
				}else{
					unset($goods_table[$key]);
				}*/

				if(isset($line['goods_id'])) $goods_ids[] = $line['goods_id'];
			}

			$qLnk = mysql_query("
								SELECT
									goods.id,
									goods.name,
									goods.alias,
									goods.present,
									goods.weight,
									goods.grower_id,
									goods.delivery_way_id,
									levels.alias AS level_alias,
									parent_tbl.alias AS parent_alias,
									parent_tbl.id AS root_id,
									growers.name AS grower
								FROM
									goods
								LEFT OUTER JOIN growers ON growers.id = goods.grower_id
								LEFT OUTER JOIN levels ON levels.id = goods.level_id
								LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
								WHERE
									goods.id IN (".implode(',',$goods_ids).")
									AND
									goods.published = 1;
								");

			while($g = mysql_fetch_assoc($qLnk)){$goods_data[$g['id']] = $g;}

			$i = 1;

			$gift = false;
			$total_goods_amount = 0;

			foreach($goods_table as $line){

				$line['num'] = $i;

				if(isset($line['goods_id'])) $gd = $goods_data[$line['goods_id']];

				$line['name'] = (($gd['grower_id']!=0) ? '«'.$gd['grower'].'». ' : '').$gd['name'];
				$line['link'] = '/'.$gd['parent_alias'].'/'.$gd['level_alias'].'/'.$gd['alias'].'/';

				//$line['amount'] = $this->ostatki_check($line['amount'],$line['goods_id'],$line['name']);
				
				//$total_goods_price+= $line['final_price']*$line['amount'];
				$total_goods_price+= $line['price']*$line['amount'];

				$line['feature_color_string'] = $this->feature_color_string($line,$goods_data);					
								
				if($line['amount']>0){
					$this->item_rq('check_goods_line',$line);
				}

				$i++;
				$total_goods_amount+=$line['amount'];
			}

			if(isset($_POST['gift_name']) && $_POST['gift_name']!='' && isset($_POST['gift_val']) && $_POST['gift_val']!=''){
				$gn_arr = explode(',',$_POST['gift_name']);
				$gn = trim($gn_arr[0]);
				
				$a = array(
						'num' => $i,
						'link' => false,
						'name' => $gn,
						'name_print' => $_POST['gift_name'],
						'discount' => 0,
						'price' => 0,
						'final_price' => 0,
						'amount' => 1,
						'barcode' => $_POST['gift_val'],
						'packing' => '',
						'feats_str' => '',
						'color' => '',
						);
				
				$this->item_rq('check_goods_line',$a);
				
				$gift = true;
			}
			
			if($total_goods_amount==0){
				$this->registry->set('no_order_allowance',true);
			}

			if($total_goods_price>=GIFT_MIN && !$gift){

				$line = array(
					'num' => $i,
					'link' => false,
					'name' => 'Подарок на усмотрение администрации',
					'discount' => 0,
					'price' => 0,
					'final_price' => 0,
					'amount' => 1,
					'barcode' => 0,
					'packing' => '',
					'feats_str' => '',
				);

				$this->item_rq('check_goods_line',$line);
			}

		}

		private function feature_color_string($line,$goods_data){
			$root_id = ($goods_data[$line['goods_id']]['root_id']);
			$label = ($root_id==4) ? 'Размер' : 'Вкус';
			
			if($line['feature']=='') return '';
			
			return ($line['color']>0)
				? sprintf('%s: %s, цвет: %s',$label,$line['feature'],$line['color_name'])
				: sprintf('%s: %s',$label,$line['feature']);			
		}
		
		private function ostatki_check($amount,$id,$name){

			$hint = isset($this->registry['ostatki_amount_hint']) ? $this->registry['ostatki_amount_hint'] : array();

			$qLnk = mysql_query("
								SELECT
									ostatki.value
								FROM
									ostatki
								WHERE
									ostatki.goods_id = '".$id."'
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$g = mysql_fetch_assoc($qLnk);
				if($g['value']<$amount){
					$amount = $g['value'];

					$hint[] = $name;

				}
			}

			if(count($hint)>0){
				$this->registry['ostatki_amount_hint'] = $hint;
			}

			return $amount;
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

		private function send_ostatok_notify($ostatki_flag,$order_id){
			if($ostatki_flag>0){
				$replace_arr = array(
					'ORDER_NUM' => $order_id,
				);

				$emails = explode('::',ADMINS_EMAILS);
				if(count($emails)>0){
					foreach($emails as $admin_mail){
						$mailer = new Mailer($this->registry,37,$replace_arr,$admin_mail,false);
					}
				}
			}
		}

		private function do_ostatki($order_id,$goods_ids){
			$rezerv = array();
			$ostatki = array();
			$qLnk = mysql_query("
								SELECT
									ostatki.*
								FROM
									ostatki
								WHERE
									ostatki.goods_id IN (".implode(",",array_keys($goods_ids)).");
								");
			if(mysql_num_rows($qLnk)>0){
				while($o = mysql_fetch_assoc($qLnk)){
					$ostatki[$o['goods_id']] = array(
						'order_amount' => $goods_ids[$o['goods_id']],
						'ostatok_amount' => $o['value'],
						'ostatok_id' => $o['id'],
					);
				}

				foreach($ostatki as $goods_id => $data){
					//апдейтим кол-во по остаткам
					mysql_query("
								UPDATE
									ostatki
								SET
									ostatki.value = ostatki.value - ".$data['order_amount']."
								WHERE
									ostatki.goods_id = '".$goods_id."';
								");

					//готовим запрос резерв
					$rezerv[] = "('".$data['ostatok_id']."','".$order_id."','".$data['order_amount']."')";

					//если больше товара не осталось - снимаем с публикации его
					if($data['order_amount']==$data['ostatok_amount']){
						mysql_query("UPDATE goods SET goods.present = 0 WHERE goods.id = '".$goods_id."';");
					}
				}

				//пишем резерв
				if(count($rezerv)>0){
					mysql_query("
								INSERT INTO
									rezerv
									(ostatok_id, order_id, amount)
									VALUES
									".implode(", ",$rezerv)."
								");
				}

			}

			return count($rezerv);
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

		private function coupon_apply(){
			if(isset($_POST['order_vals']['coupon']) && $_POST['order_vals']['coupon']!='' && mb_strlen($_POST['order_vals']['coupon'],'utf-8')==5){
				$qLnk = mysql_query("
									SELECT
										coupons.percent
									FROM
										coupons
									WHERE
										coupons.hash = '".$_POST['order_vals']['coupon']."'
										AND
										coupons.status = 1
									LIMIT 1;
									");
				if(mysql_num_rows($qLnk)>0){
					$UD = $this->registry['userdata'];
					$UD['personal_discount'] = mysql_result($qLnk,0);

					$this->registry['userdata'] = $UD;
					$this->registry->set('coupon_discount',$UD['personal_discount']);
					return true;
				}
			}

			$this->registry->set('coupon_discount',0);
		}

		private function wishesphone_2_cookie(){
			$w = (isset($_POST['order_vals']['wishes'])) ? $_POST['order_vals']['wishes'] : '';
			setcookie('cart_wishes',$w,time()+3600,'/');

			$p = (isset($_POST['phone'])) ? $_POST['phone'] : '';
			setcookie('cart_courier_phone',$p,time()+3600,'/');
		}

		public function check_if_spb(){
			$zip_code = trim($this->registry['userdata']['zip_code']);
			$qLnk = mysql_query("
								SELECT
									IF(indexes.region='Санкт-Петербург',1,0) AS is_spb
								FROM
									indexes
								WHERE
									indexes.ind = '".$zip_code."'
									OR
									indexes.ind_old = '".$zip_code."'
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$this->registry['is_spb'] = mysql_result($qLnk,0);
			}else{
				$this->registry['is_spb'] = 0;
			}
		}

		public function delivery_payment_match_check(){
			if(isset($_POST['order_vals']['delivery']) && isset($_POST['order_vals']['payment']) && isset($this->delivery_payment_match[$_POST['order_vals']['delivery']]) && in_array($_POST['order_vals']['payment'],$this->delivery_payment_match[$_POST['order_vals']['delivery']])){
				return true;
			}else{
				return false;
			}
		}

		public function mail_mismatch(){

			ob_start();
			echo '<pre>';
			print_r($_POST);
			echo '</pre>';
			$order_post = ob_get_clean();

			$replace_arr = array(
				'BROWSER' => $_SERVER['HTTP_USER_AGENT'],
				'CLIENT' => $this->registry['userdata']['login'],
				'TIME' => date('d.m.Y H:i',time()),
				'ORDER_POST' => $order_post,
			);

			$emails = explode('::',ADMINS_EMAILS);
			if(count($emails)>0){
				foreach($emails as $admin_mail){
					$mailer = new Mailer($this->registry,29,$replace_arr,$admin_mail,false);
				}
			}
		}

		public function print_payment_match(){
			foreach($this->delivery_payment_match[$_POST['order_vals']['delivery']] as $pt){
				if(isset($this->payment_types[$pt]['name'])){
					echo '<li>'.$this->payment_types[$pt]['name'].'</li>';
				}
			}
		}

	}
?>