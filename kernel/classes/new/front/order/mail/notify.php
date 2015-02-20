<?php
Class Front_Order_Mail_Notify Extends Common_Rq{
	
	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	private function print_goods_table($order){
		
		$lines = array();
		foreach($order['goods'] as $g){
			$g['url'] = sprintf('%s%s/%s/%s',
					THIS_URL,
					$g['parent_alias'],
					$g['level_alias'],
					$g['alias']
					);
			
			$g['final_sum'] = $g['final_price']*$g['amount'];
			
			$lines[] = $this->do_rq('line',$g,true);
		}
		
		$a = array(
				'lines' => implode('',$lines),
				'sum' => Common_Useful::price2read(0),
				'discount_amount' => Common_Useful::price2read(0),
				'sum_with_discount' => Common_Useful::price2read(0)
				);
		
		return $this->do_rq('table',$a);
	}
	
	private function tr_comp_payment($order){
		if($order['payment_method']=='W')
			return 'через WebMoney, Яндекс-деньги';
		elseif($order['by_card']==1)
			return 'банковской картой';
		else
			return 'предоплата';	
	}
	
	private function additional_payment($order){
		if($order['from_account']!=$order['overall_price'] && $order['from_account']){
			return 'С Вашего личного счета удержано '.Common_Useful::price2read($order['from_account']).' руб., <b>к оплате '.Common_Useful::price2read($order['overall_price']-$order['from_account']).' руб.</b>';
		}		
	}
	
	public function send_letter($order,$direction = 0){
					
		$replace = array(
			'ORDER_NUM' => $order['num'],
			'ADMIN_ORDER_SUM' => '',
			'ORDER_DATE' => date('d.m.Y',strtotime($order['made_on'])),
			'USER_NAME' => $order['user_name'],
			'USER_ADDRESS' => Common_Address::implode_address($order),
			'USER_EMAIL' => $order['user_email'],
			'USER_ID' => $order['user_id'],
			'ORDER_TABLE' => $this->print_goods_table($order),
			'ORDER_DELIVERY_COSTS' => 0,
			'NALOG_COSTS' => 0,
			'OVERALL_PRICE' => 0,
			'OVERALL_PRICE_CORR' => 0,
			'PHOHE_NUMBER' => $order['phone_number'],
			'WISHES' => ($order['wishes']) ? 'Ваши пожелания: '.$order['wishes'] : '',
			'ADDITIONAL_PAYMENT' => $this->additional_payment($order),
			'TR_COMP_PAYMENT' => $this->tr_comp_payment($order),
			'COURIER_MIN' => FREE_DELIVERY_SUM,
		);

		/*if(isset($_GET['tp']) && $_GET['tp']==4){
			$replace_arr['PAYMENT_METHOD'] = 'по банковской карте';
		}elseif(isset($_GET['tp']) && $_GET['tp']==7){
			$replace_arr['PAYMENT_METHOD'] = 'платежные системы';
		}*/

		$tpl_id = $this->get_tpl($order);

		//только покупателям
		if($direction==1){
			$this->to_guests($tpl_id,$replace,$order);
			
		//только менеджерам	
		}elseif($direction==2){
			$this->to_managers($tpl_id,$replace,$order);
			
		//всем	
		}else{
			$this->to_guests($tpl_id,$replace,$order);
			$this->to_managers($tpl_id,$replace,$order);
		}
			
	}
	
	private function to_guests($tpl_id,$replace,$order){
		$mailer = new Mailer($this->registry,$tpl_id,$replace,$order['user_email']);
	}

	private function to_managers($tpl_id,$replace,$order){
		$emails = explode('::',ADMINS_EMAILS);
		
		$replace['ADMIN_ORDER_SUM'] = '<span style="color:#999;font-size:13px;font-weight:normal;">'.$replace['OVERALL_PRICE'].' руб.<span>';
		
		foreach($emails as $admin_mail)
			$mailer = new Mailer($this->registry,$tpl_id,$replace,$admin_mail);	
	}	
	
	private function get_tpl($order){
	
		if($order['delivery_type']==3){
			$tpl_id = 26;
		}elseif($order['delivery_type']==4){
			$tpl_id = 39;
		}else{
	
			if($order['by_card']==1){
				if($order['delivery_type']==1){
					$tpl_id = 33;
				}else{
					$tpl_id = 34;
				}
			}else{
				if($order['payment_method']=='П'){
					if($order['delivery_type']==1){
						$tpl_id = ($order['from_account']!=$order['overall_price']) ? 4 : 16;
					}elseif($order['delivery_type']==2 || $order['delivery_type']==3){
						if($order['pay2courier']==1){
							$tpl_id = 18;
						}else{
							$tpl_id = ($order['from_account']!=$order['overall_price']) ? 17 : 20;
						}
					}
				}elseif($order['payment_method']=='W'){
					if($order['delivery_type']==1){
						$tpl_id = 15;
					}elseif($order['delivery_type']==2){
						$tpl_id = 19;
					}
				}elseif($order['payment_method']=='Н'){
					$tpl_id=14;
				}
			}
	
		}
	
		return $tpl_id;
	}	
	
	
}
?>