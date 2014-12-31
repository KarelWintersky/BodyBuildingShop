<?
	Class Logic{

		private $registry;

		public function __construct($registry){
			$this->registry = $registry;
		}

		public function item_rq($name,$a = NULL){
			require($this->registry['template']->TF.'item/logic/'.$name.'.html');
		}

		public function register_params(){
			$qLnk = mysql_query("SELECT params.name, params.value FROM params");
			while($p = mysql_fetch_assoc($qLnk)){
				define($p['name'],$p['value'],true);
			}
		}

		public function get_index_data($zip_code){
			$qLnk = mysql_query("
								SELECT
									indexes.ind,
									indexes.region,
									indexes.city,
									indexes.type_ogr,
									indexes.tarif_pos_basic,
									indexes.tarif_pos_add,
									indexes.tarif_band_basic,
									indexes.tarif_band_add,
									indexes.type_dost,
									indexes.tarif_post_avia_pos,
									indexes.tarif_avia_pos,
									indexes.tarif_post_avia_band,
									indexes.tarif_avia_band,
									indexes.city,
									IF(indexes.city='г.Санкт-Петербург',1,0) AS is_spb
								FROM
									indexes
								WHERE
									indexes.ind = '".$zip_code."'
									OR
									indexes.ind_old = '".$zip_code."'
									AND
									DATE(NOW())
										BETWEEN
											DATE(CONCAT_WS('-',YEAR(NOW()),SUBSTRING_INDEX(SUBSTRING_INDEX(indexes.time_ogr,'-',1),'.',-1),SUBSTRING_INDEX(SUBSTRING_INDEX(indexes.time_ogr,'-',1),'.',1)))
										AND
											DATE(CONCAT_WS('-',YEAR(NOW()),SUBSTRING_INDEX(SUBSTRING_INDEX(indexes.time_ogr,'-',-1),'.',-1),SUBSTRING_INDEX(SUBSTRING_INDEX(indexes.time_ogr,'-',-1),'.',1)))
								");
			if(mysql_num_rows($qLnk)>0){
				$zc_data = mysql_fetch_assoc($qLnk);
			}else{
				$zc_data = false;
			}

			return $zc_data;
		}

		private function zip_code_query($zip_code,$field){
			return "SELECT
									indexes.ind,
									indexes.ind_old,
									indexes.region,
									indexes.city,
									indexes.type_ogr,
									indexes.tarif_pos_basic,
									indexes.tarif_pos_add,
									indexes.tarif_band_basic,
									indexes.tarif_band_add,
									indexes.type_dost,
									indexes.tarif_post_avia_pos,
									indexes.tarif_avia_pos,
									indexes.tarif_post_avia_band,
									indexes.tarif_avia_band
								FROM
									indexes
								WHERE
									indexes.".$field." = '".$zip_code."'
									AND
									DATE(NOW())
										BETWEEN
											DATE(CONCAT_WS('-',YEAR(NOW()),SUBSTRING_INDEX(SUBSTRING_INDEX(indexes.time_ogr,'-',1),'.',-1),SUBSTRING_INDEX(SUBSTRING_INDEX(indexes.time_ogr,'-',1),'.',1)))
										AND
											DATE(CONCAT_WS('-',YEAR(NOW()),SUBSTRING_INDEX(SUBSTRING_INDEX(indexes.time_ogr,'-',-1),'.',-1),SUBSTRING_INDEX(SUBSTRING_INDEX(indexes.time_ogr,'-',-1),'.',1)))";
		}

		public function zip_code_find($zip_code,&$flag){
			$qLnk = mysql_query($this->zip_code_query($zip_code,'ind'));

			if(mysql_num_rows($qLnk)>0){
				$this->registry['zc_data'] = mysql_fetch_assoc($qLnk);
				$flag = true;
			}else{

				$qLnk = mysql_query($this->zip_code_query($zip_code,'ind_old'));
				if(mysql_num_rows($qLnk)>0){
					 $zc_data = mysql_fetch_assoc($qLnk);
					 $zc_data['in_old_index'] = true;
					 $this->registry['zc_data'] = $zc_data;
					$flag = true;
				}else{
					$flag = false;
				}
			}

		}

		public function zip_code_data($zip_code){

			$flag = true;

			$delivery_statuses = array(
				0 => 'В настоящий период времени доставка к Вам невозможна',
				1 => 'В настоящий период времени к Вам возможна комбинированная доставка - сначала наземным, затем авиатранспортом',
				2 => 'В настоящий период времени к Вам возможна комбинированная доставка только авиатранспортом',
				3 => 'В настоящий период времени доставка к Вам возможна наземным транспортом',
			);

			$ogr_types = array(
				1 => 'Вы проживаете в труднодоступном регионе, в который периодически запрещается прием посылок для пересылки наземным транспортом. Авиа-доставка в Ваш регион ОТСУТСТВУЕТ!',
				2 => 'Вы проживаете в труднодоступном регионе, в который периодически запрещается прием  посылок для пересылки наземным транспортом. Авиа-доставка ЕСТЬ.',
				3 => 'Вы проживаете в труднодоступном регионе, в который доставка почтовых отправлений осуществляется только авиа-транспортом. Поэтому Вы можете делать заказы только ПО ПРЕДОПЛАТЕ или с оплатой через систему WebMoney',
			);

			if(!isset($this->registry['zc_data'])){
				$this->zip_code_find($zip_code,$flag);
			}

			if($flag){
				$zc_data = $this->registry['zc_data'];
				$zc_data['delivery_status'] = $delivery_statuses[$zc_data['type_dost']];
				$zc_data['ogr_type'] = isset($ogr_types[$zc_data['type_ogr']]) ? $ogr_types[$zc_data['type_ogr']] : '';
			}

			require($this->registry['template']->TF.'item/profile/zip_code_data.html');

		}

		public function send_account_order($order_id){
			$qLnk = mysql_query("
								SELECT
									account_orders.*,
									users.name AS user_name,
									users.email AS user_email,
									users.zip_code AS zip_code,
									users.region AS region,
									users.city AS city,
									users.street AS street,
									users.house AS house,
									users.corpus AS corpus,
									users.flat AS flat
								FROM
									account_orders
								LEFT OUTER JOIN users ON users.id = account_orders.user_id
								WHERE
									account_orders.id = '".$order_id."'
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$order = mysql_fetch_assoc($qLnk);

				$order['address'] = $this->registry['logic']->implode_address($order);
				$order['num'] = $order_id.'/'.$order['user_num'].'/A';
				$order['overall_price'] = $order['sum'];
				$order['from_account'] = 0;

				ob_start();
				$this->item_rq('bill',$order);
				$bill = ob_get_contents();
				ob_end_clean();

				$pdfmanager = new Pdfmanager($this->registry);
				$attach_string = $pdfmanager->fileCompose($bill);

				$replace_arr = array(
					'USER_NAME' => $order['user_name'],
					'ORDER_NUM' => $order['num']
				);

				$mailer = new Mailer($this->registry,25,$replace_arr,$order['user_email'],$attach_string);

			}
		}

		public function send_bill($num){
			$order_num = explode('/',$num);

			$qLnk = mysql_query("
								SELECT
									orders.*,
									users.name AS user_name,
									users.email AS user_email,
									users.zip_code AS zip_code,
									users.region AS region,
									users.city AS city,
									users.street AS street,
									users.house AS house,
									users.corpus AS corpus,
									users.flat AS flat
								FROM
									orders
								LEFT OUTER JOIN users ON users.id = orders.user_id
								WHERE
									orders.id = '".$order_num[0]."'
									AND
									orders.user_num	= '".$order_num[1]."'
									AND
									orders.payment_method = '".$order_num[2]."'
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$order = mysql_fetch_assoc($qLnk);
				$order['address'] = $this->implode_address($order);
				$order['num'] = $num;

				ob_start();
				$this->item_rq('bill',$order);
				$bill = ob_get_contents();
				ob_end_clean();

				$pdfmanager = new Pdfmanager($this->registry);
				$attach_string = $pdfmanager->fileCompose($bill);

				$replace_arr = array(
					'ORDER_NUM' => $num
				);

				$mailer = new Mailer($this->registry,13,$replace_arr,$order['user_email'],$attach_string);

			}

		}

		public function send_order($num,$to_admins = false,$to_users = true){
			$order_num = explode('/',$num);
			$qLnk = mysql_query("
								SELECT
									orders.*,
									users.name AS user_name,
									users.email AS user_email,
									users.zip_code AS zip_code,
									users.region AS region,
									users.city AS city,
									users.street AS street,
									users.house AS house,
									users.corpus AS corpus,
									users.flat AS flat,
									users.personal_discount AS personal_discount
								FROM
									orders
								LEFT OUTER JOIN users ON users.id = orders.user_id
								WHERE
									orders.id = '".$order_num[0]."'
									AND
									orders.user_num	= '".$order_num[1]."'
									AND
									orders.payment_method = '".$order_num[2]."'
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$order = mysql_fetch_assoc($qLnk);

				
				
				$goods = array();
				$barcodes = array();
				$qLnk = mysql_query("
						SELECT
							orders_goods.*
						FROM
							orders_goods
						WHERE
							orders_goods.order_id = '".$num."'
						ORDER BY
							orders_goods.final_price DESC;
						");
			if(mysql_num_rows($qLnk)>0){
	
				while($g = mysql_fetch_assoc($qLnk)){
					$goods[] = $g;
					$barcodes[] = $g['goods_barcode'];
				}
					
				$is_barcodes = false;
				foreach($barcodes as $b) if($b!='') $is_barcodes = true;
									
				if($is_barcodes){
				
					foreach($goods as $key => $g){
						if($g['goods_feats_str']!='' && is_numeric($g['goods_feats_str'])){
							$qLnk = mysql_query(sprintf("SELECT IFNULL(name,'') FROM features WHERE id = '%d'",$g['goods_feats_str']));
							$goods[$key]['goods_feats_str'] = (mysql_num_rows($qLnk)>0) ? mysql_result($qLnk,0) : '';
						}
					}
					
					$qLnk = mysql_query(sprintf("
							SELECT
								goods_barcodes.barcode,
								goods_barcodes.feature,
								goods_barcodes.packing,
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
								$goods[$key]['packing'] = $g['packing'];
								$goods[$key]['parent_parent_id'] = $g['parent_parent_id'];								
							}
						}
					}
					
				}else{
					$ids = array();
					foreach($goods as $g) $ids[] = $g['goods_id'];
					
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
							orders_goods.id IN (%s)
							",
							implode(",",$ids)
					));
					while($g = mysql_fetch_assoc($qLnk)){
						$goods[$g['id']]['alias'] = $g['alias'];
						$goods[$g['id']]['level_alias'] = $g['level_alias'];
						$goods[$g['id']]['parent_alias'] = $g['parent_alias'];
					}
				}				
				
					ob_start();
					$this->item_rq('mailorder/thead');
					$sum = 0;
										
					foreach($goods as $g){
						//$goods[$g['goods_id']] = $g['goods_full_name'];

						$g['final_sum'] = $g['final_price']*$g['amount'];
						$sum+=$g['final_sum'];
						$this->item_rq('mailorder/tline',$g);
					}

					$ostatki = array();
					$qLnk = mysql_query("
										SELECT
											ostatki.*
										FROM
											ostatki
										WHERE
											ostatki.goods_id IN (".implode(",",array_keys($goods)).");
										");
					while($o = mysql_fetch_assoc($qLnk)){
						$ostatki[] = $goods[$o['goods_id']];
					}

					$discount = ($order['coupon_discount']>0) ? $order['coupon_discount'] : $order['personal_discount'];

					$a = array(
						'sum' => $sum,
						'personal_discount' => $discount,
						'discount_amount' => round($sum*$discount/100),
					);

					$sum = $a['sum'] - $a['discount_amount'];

					$this->item_rq('mailorder/tsummary',$a);
					echo '</table>';
					$order_table = ob_get_contents();
					ob_end_clean();

					if($order['from_account']!=$order['overall_price'] && $order['from_account']>0){
						$additional_payment = 'С Вашего личного счета удержано '.Common_Useful::price2read($order['from_account']).' руб., <b>к оплате '.Common_Useful::price2read($order['overall_price']-$order['from_account']).' руб.</b>';
					}else{
						if(count($ostatki)>0){
							$additional_payment = 'Поскольку в вашей корзине присутствует товар <b>'.implode(', ',$ostatki).'</b>, количество которого строго ограничено и резервируется под заказ, то Вам будет доступна только предоплата.<br><br>Заказ Вы должны будете оплатить в течении '.REZERV_ORDER_DAYS.' дней и ОБЯЗАТЕЛЬНО сообщить нам о факте оплаты е-мэйлом. В противном случае, через '.REZERV_ORDER_DAYS.' дней если от вас не поступят деньги или уведомление об оплате - резерв на товар будет снят и он вернется в продажу.';
						}else{
							$additional_payment = '';
						}
					}

					//ORDER_DELIVERY_COSTS
					if($order['delivery_costs']>0){
						$order_delivery_costs = $order['delivery_costs'].' руб.';
					}else{
						if($order['delivery_type']==2){
							$qLnk = mysql_query("
												SELECT
													IF(indexes.region='Санкт-Петербург',1,0) AS is_spb
												FROM
													indexes
												WHERE
													indexes.ind = '".$order['zip_code']."'
													OR
													indexes.ind_old = '".$order['zip_code']."'
												LIMIT 1;
												");
							$is_spb = (mysql_num_rows($qLnk)>0) ? mysql_result($qLnk,0) : 0;
							if($is_spb==1){
								$order_delivery_costs = 'бесплатна';
							}else{
								$order_delivery_costs = 'уточняется';
							}
						}else{
							$order_delivery_costs = 'уточняется';
						}
					}

					//TR_COMP_PAYMENT
					if($order['payment_method']=='W'){
						$tr_comp_payment = 'через WebMoney, Яндекс-деньги';
					}elseif($order['by_card']==1){
						$tr_comp_payment = 'банковской картой';
					}else{
						$tr_comp_payment = 'предоплата';
					}
										
					$nalog_costs = ($order['payment_method']=='H' || $order['payment_method']=='Н') ? round($sum*0.3) : 0;

					$overall_sum = $sum + $nalog_costs + $order_delivery_costs;
					
					$replace_arr = array(
						'ORDER_NUM' => $num,
						'ADMIN_ORDER_SUM' => '',
						'ORDER_DATE' => date('d.m.Y',strtotime($order['made_on'])),
						'USER_NAME' => $order['user_name'],
						'USER_ADDRESS' => $this->implode_address($order),
						'USER_EMAIL' => $order['user_email'],
						'USER_ID' => $order['user_id'],
						'ORDER_TABLE' => $order_table,
						'ORDER_DELIVERY_COSTS' => $order_delivery_costs,
						'NALOG_COSTS' => $nalog_costs,
						'OVERALL_PRICE' => $overall_sum,
						'OVERALL_PRICE_CORR' => $order_delivery_costs+$sum-$order['from_account'],
						'PHOHE_NUMBER' => $order['phone_number'],
						'WISHES' => ($order['wishes']!='') ? 'Ваши пожелания: '.$order['wishes'] : '',
						'ADDITIONAL_PAYMENT' => $additional_payment,
						'TR_COMP_PAYMENT' => $tr_comp_payment,
						'COURIER_MIN' => FREE_DELIVERY_SUM,
					);

					if(isset($_GET['tp']) && $_GET['tp']==4){
						$replace_arr['PAYMENT_METHOD'] = 'по банковской карте';
					}elseif(isset($_GET['tp']) && $_GET['tp']==7){
						$replace_arr['PAYMENT_METHOD'] = 'платежные системы';
					}

					$tpl_id = $this->get_mail_tpl_id($order);

					if($to_users):
						$mailer = new Mailer($this->registry,$tpl_id,$replace_arr,$order['user_email']);
					endif;

					/**/
					if($to_admins){
						$emails = explode('::',ADMINS_EMAILS);
						if(count($emails)>0){
							foreach($emails as $admin_mail){
								$replace_arr['ADMIN_ORDER_SUM'] = '<span style="color:#999;font-size:13px;font-weight:normal;">'.$replace_arr['OVERALL_PRICE'].' руб.<span>';
								$mailer = new Mailer($this->registry,$tpl_id,$replace_arr,$admin_mail);
							}
						}
					}
					/**/
				}
			}

		}

		private function get_mail_tpl_id($arr){

			if($arr['delivery_type']==3){
				$tpl_id = 26;
			}elseif($arr['delivery_type']==4){
				$tpl_id = 39;
			}else{

				if($arr['by_card']==1){
					if($arr['delivery_type']==1){
						$tpl_id = 33;
					}else{
						$tpl_id = 34;
					}
				}else{
					if($arr['payment_method']=='П'){
						if($arr['delivery_type']==1){
							$tpl_id = ($arr['from_account']!=$arr['overall_price']) ? 4 : 16;
						}elseif($arr['delivery_type']==2 || $arr['delivery_type']==3){
							if($arr['pay2courier']==1){
								$tpl_id = 18;
							}else{
								$tpl_id = ($arr['from_account']!=$arr['overall_price']) ? 17 : 20;
							}
						}
					}elseif($arr['payment_method']=='W'){
						if($arr['delivery_type']==1){
							$tpl_id = 15;
						}elseif($arr['delivery_type']==2){
							$tpl_id = 19;
						}
					}elseif($arr['payment_method']=='Н'){
						$tpl_id=14;
					}
				}

			}

			return $tpl_id;
		}

		public function implode_address($a){
			$address = $a['zip_code'].', Россия, '.$a['city'].', '.$a['street'].', д. '.$a['house'];
			if($a['corpus']!=''){$address.= ', корп. '.$a['corpus'];}
			if($a['flat']!=''){$address.= ', кв. '.$a['flat'];}
			return $address;
		}

		public function admins_notify($order_id){
			$order_data = $this->admins_tech_letter($order_id);

			
			$order_num_subj_PM = (strpos($order_id,'П'!==false)) 
				? ' ПО ПРЕДОПЛАТЕ' 
				: '';
			
			$order_num_subj = $order_id.$order_num_subj_PM;

			$emails = explode('::',ADMINS_EMAILS);
			if(count($emails)>0){
				foreach($emails as $admin_mail){
					$replace_arr = array(
						'ORDER_DATA' => iconv('utf-8','windows-1251',$order_data),
						'ORDER_NUM_SUBJ' => iconv('utf-8','windows-1251',$order_num_subj),
					);

					$mailer = new Mailer($this->registry,12,$replace_arr,$admin_mail,false,'windows-1251');

					if($this->registry['paid_from_account']){
						$subj = ($this->registry['paid_from_account']==1) ? 'Заказ '.$order_id.' оплачен со счета полностью' : 'Заказ '.$order_id.' оплачен со счета частично';
						$replace_arr['ORDER_NUM_SUBJ'] = iconv('utf-8','windows-1251',$subj);
						$replace_arr['FROM_ACCOUNT'] = $this->registry['from_account_p'];
						$replace_arr['REST'] = ($this->registry['overall_p'] - $this->registry['from_account_p']);
						$mailer = new Mailer($this->registry,30,$replace_arr,$admin_mail,false,'windows-1251');
					}

				}
			}

		}

		private function admins_tech_letter($order_id){
			$lines = array();
			$order_num = explode('/',$order_id);
			$qLnk = mysql_query("
								SELECT
									orders.*,
									users.login AS login,
									users.email AS email,
									users.name AS name,
									users.zip_code AS zip_code,
									users.city AS city,
									users.street AS street,
									users.house AS house,
									users.corpus AS corpus,
									users.flat AS flat,
									users.personal_discount AS personal_discount
								FROM
									orders
								LEFT OUTER JOIN users ON users.id = orders.user_id
								WHERE
									orders.id = '".$order_num[0]."'
									AND
									orders.user_num	= '".$order_num[1]."'
									AND
									orders.payment_method = '".$order_num[2]."'
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$o = mysql_fetch_assoc($qLnk);

				$this->registry['paid_from_account'] = ($o['overall_price']>0 && $o['overall_price']==$o['from_account']) ? 1 : (($o['from_account']>0) ? 2 : false);
					$this->registry['from_account_p'] = $o['from_account'];
					$this->registry['overall_p'] = $o['overall_price'];
					$this->registry['by_card'] = $o['by_card'];

				$address = $o['zip_code'].', Россия, '.$o['city'].', '.$o['street'].', д. '.$o['house'].', корп. '.$o['corpus'].', кв. '.$o['flat'];

				if($o['delivery_type']==1){
					$delivery_type = 'почтой';
				}elseif($o['delivery_type']==2){
					$delivery_type = 'курьером';
				}elseif($o['delivery_type']==4){
					$delivery_type = 'самовывоз';
				}else{
					$delivery_type = 'транспортной компанией';
				}

				$phone_number = ($o['phone_number']!='') ? $o['phone_number'] : ' ';

				//собираем способ оплаты
				if($o['payment_method']=='W'){
					$PM = 'электронные деньги';
				}elseif($o['payment_method']=='H' || $o['payment_method']=='Н'){
					$PM = 'наложенный платеж';
				}else{
					if($o['pay2courier']==1){
						$PM = 'наличными курьеру';
					}elseif($o['overall_price']==$o['from_account']){
						$PM = 'полностью со счета';
					}elseif($o['by_card']==1){
						$PM = 'по банковской карте';
					}else{
						$PM = 'в банке по квитанции';
					}
				}

				$lines[] = $order_id.'::'.$o['user_id'].'::'.$o['login'].'::'.date('d.m.Y',strtotime($o['made_on'])).'::'.date('H:i:s',strtotime($o['made_on'])).'::'.$o['delivery_costs'].'::'.$PM.'::'.$delivery_type.'::'.$o['overall_price'].'::'.$o['email'].'::'.$o['name'].'::'.$address.'::'.$_SERVER['HTTP_USER_AGENT'].'::'.$phone_number.'::'.$o['wishes'];

				$qLnk = mysql_query("
									SELECT
										orders_goods.*
									FROM
										orders_goods
									WHERE
										orders_goods.order_id = '".$order_id."';
									");
				while($g = mysql_fetch_assoc($qLnk)){
					
					/*if($g['goods_feats_str']!=''){

						$feats_final = array();
						$feats = explode(',',$g['goods_feats_str']);
						foreach($feats as $f){
							$f = trim($f);
							$f = explode(':',$f);
							if(count($f)==2){
								$feats_final[] = trim($f[1]);
							}
						}
						$feats_final = implode(', ',$feats_final);

					}else{
						$feats_final = ' ';
					}*/

					$feats_final = $g['goods_feats_str'];
					$feats_final = ($feats_final=='') ? ' ' : $feats_final;
										
					$FP = $g['final_price'] - intval($g['final_price']*$o['personal_discount']/100); //цену указываем с персональной скидкой (хранится без нее)

					$barcode = ($g['goods_barcode']!=0) ? $g['goods_barcode'] : 'any';

					$lines[] = $barcode.'::'.$feats_final.'::'.$g['amount'].'::'.$g['discount'].'::'.$FP;
				}
				$tech = implode("<br>",$lines);
			}else{
				$tech = '';
			}

			return $tech;

		}

	}
?>