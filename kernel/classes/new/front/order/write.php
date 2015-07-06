<?php
Class Front_Order_Write{

	/*
	 * основной функционал записи заказа в базу
	 * */
	
	private $registry;
	
	private $Front_Order_Data;
	private $Front_Order_Write_Input;
	private $Front_Order_Write_Query;
	private $Front_Order_Write_Coupon;
	private $Front_Order_Write_Goods;
	
	private $Front_Catalog_Goods_Rate;
	
	private $Front_Order_Mail;
		
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Data = new Front_Order_Data($this->registry);
		$this->Front_Order_Write_Input = new Front_Order_Write_Input($this->registry);
		$this->Front_Order_Write_Query = new Front_Order_Write_Query($this->registry);
		$this->Front_Order_Write_Coupon = new Front_Order_Write_Coupon($this->registry);
		$this->Front_Order_Write_Goods = new Front_Order_Write_Goods($this->registry);
		
		$this->Front_Catalog_Goods_Rate = new Front_Catalog_Goods_Rate($this->registry);
		
		$this->Front_Order_Mail = new Front_Order_Mail($this->registry);
	}	
			
	private function user_num(){
		//для незарегистрированных - всегда 1
		if(!$this->registry['userdata']) return 1;
				
		$qLnk = mysql_query(sprintf("
				SELECT 
					IFNULL(MAX(user_num),0)+1 
				FROM 
					orders 
				WHERE 
					user_id = '%d'",
				$this->registry['userdata']['id']
				));
		
		return mysql_result($qLnk,0);		
	}
	
	private function payment_method_code($payment_method){
		if($payment_method==1) return 'Н';
		elseif($payment_method==3) return 'W';
		else return 'П';
	}
	
	private function get_payment_number($payment_method){
		/*
		 * номер заказа определенного вида оплат
		* */
	
		if($payment_method==1) $param = 'LAST_ORDER_N';
		elseif($payment_method==3) $param = 'LAST_ORDER_W';
		else $param = 'LAST_ORDER_P';
	
		$qLnk = mysql_query(sprintf("
				SELECT
					(value+1)
				FROM
					params
				WHERE
					name = '%s';",
				$param
		));
		$id = mysql_result($qLnk,0);
	
		//и сразу резвервируем этот номер в базе
		mysql_query(sprintf("
				UPDATE
					params
				SET
					value = '%d'
				WHERE
					name = '%s'",
				$id,
				$param
		));
	
		return $id;
	}	
	
	private function dissmiss_from_account($query){
		/*
		 * списываем средства со счета юзера
		 * */
		if(!$this->registry['userdata']) return false;
		
		mysql_query(sprintf("
				UPDATE 
					users 
				SET 
					my_account = (my_account - %s) 
				WHERE 
					id = '%d'",
				$query['from_account'],
				$this->registry['userdata']['id']
				));
	}
	
	private function instant_payment($payment_method){
		//если предоплата сразу же со счета, пересчитываем скиду и наложку. А также популярность
		
		if($payment_method!=6) return false;
		
		$order_arr = explode('/',$order_id);
		$blocks = new Blocks($this->registry,false);
		$mail_nalog = $blocks->order_nalog($order_arr,1);
		$mail_discount = $blocks->mk_discount($order_arr,1);
		
		if($mail_nalog || $mail_discount) $this->mail_user_data_change($mail_nalog,$mail_discount);
		
		$this->Front_Catalog_Goods_Rate->rate_restruct();	
	}
	
	private function truncate_cart_and_storage(){
		setcookie('thecart','',time()-3600,'/');
		
		$this->registry['CL_storage']->truncate_storage();
		
		Front_Order_Steps::write_submit(1,true);
	}
	
	private function go_further($by_card,$order_num){
		$this->Front_Order_Mail->send_mail($order_num);
		
		$url = ($by_card)
			? sprintf('/order/card/prepare/?id=%s',$order_num)
			: Front_Order_Helper::done_link($order_num);
				
		header(sprintf('Location: %s',$url));
		exit();
	}
	
	private function discount_percent($input){
		$personal_discount = ($this->registry['userdata']) ? $this->registry['userdata']['personal_discount'] : 0;
		
		return ($input['coupon_discount']) ? $input['coupon_discount'] : $personal_discount;
	}
	
	private function from_account($input){
		/*
		 * рассчитываем сумму оплаты со счета
		 * - если выбран другой способ оплаты - 0
		 * - если суммы на счету хватает на заказ - сумма заказа
		 * - если суммы на счету не хватает на заказ - сумма на счету
		 * */
		
		if($input['payment_method']!=6 || !$this->registry['userdata']) return 0;
		
		return ($input['overall_sum']<=$this->registry['userdata']['my_account']) 
			? $input['overall_sum']
			: $this->registry['userdata']['my_account'];
	}
	
	private function order_status($input){
		/*
		 * статус заказа при записи
		 * если оплата с личного счета и ЦЕЛИКОМ - 3 (оплачен)
		 * в любом другом случае - 1 (сформирован)
		 * */
		
		if(!$this->registry['userdata']) return 1;
		
		return ($input['payment_method']==6 && $input['overall_sum']<=$this->registry['userdata']['my_account']) ? 3 : 1;
	}
	
	public function do_write(){		
		$data = $this->Front_Order_Data->get_data();
		$input = $this->Front_Order_Write_Input->make_data($data);
				
		$query = $input + array(
			'user_num' => $this->user_num(),
			'payment_method_code' => $this->payment_method_code($input['payment_method']),
			'payment_number' => $this->get_payment_number($input['payment_method']),
			'order_status' => $this->order_status($input),
			'payed_on' => ($input['payment_method']==6) ? "NOW()" : "0000-00-00",
			'user_id' => ($this->registry['userdata']) ? $this->registry['userdata']['id'] : 0,
			'by_card' => ($input['payment_method']==7 || $input['account_extra_payment']==7),
			'discount_percent' => $this->discount_percent($input),
			'from_account' => $this->from_account($input),
			'pay2courier' => ($input['payment_method']==5), 
				);
		
		$this->Front_Order_Write_Query->do_query($query);

		$order_num = sprintf('%d/%d/%s',
				$query['payment_number'],
				$query['user_num'],
				$query['payment_method_code']
				);

		$this->Front_Order_Write_Goods->do_write($order_num,$data);
		
		$this->Front_Order_Write_Coupon->truncate_coupon($input['coupon'],$order_num);

		$this->dissmiss_from_account($query);
				
		$this->truncate_cart_and_storage();

		$this->go_further($query['by_card'],$order_num);
	}

	/*private function mail_user_data_change($mail_nalog,$mail_discount){
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
	
	}*/	
	
}
?>