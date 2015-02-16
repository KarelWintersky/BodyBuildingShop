<?php
Class Front_Order_Mail_Tech{
	
	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
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