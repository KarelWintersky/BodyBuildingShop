<?php
Class Front_Order_Mail_Data{
	
	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	private function get_goods($num){
		
	}
	
	public function get_data($num){
		$num = explode('/',$num);
		if(count($num)!=3) return false;
			
		$qLnk = mysql_query(sprintf("
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
								orders.id = '%d'
								AND
								orders.user_num	= '%d'
								AND
								orders.payment_method = '%s'
							LIMIT 1;
							",
						$num[0],
						$num[1],
						mysql_real_escape_string($num[2])
						));
		$order = mysql_fetch_assoc($qLnk);
		if(!$order) return false;
		
		$this->get_goods($num);
		
		
		
		
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
?>