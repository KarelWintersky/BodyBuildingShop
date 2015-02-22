<?php
Class Adm_Orders_Save{

	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}
		
	/*public function resend_message(){
	 $this->registry['logic']->send_order($_POST['num'],true,false);
	$this->registry['logic']->admins_notify($_POST['num']);
	}
	
	public function resend_bill(){
	$this->registry['logic']->send_bill($_POST['num']);
	}	*/	
	
	public function order_sav(){
		foreach($_POST as $key => $val){
			$$key = (is_array($val)) ? $val : $val;
		}
	
		$id_arr = explode('/',$num);
	
		$postnum = ($postnum!='') ? "'".$postnum."'" : "NULL";
	
		mysql_query("
				UPDATE
				orders
				SET
				orders.status = '".$status."',
				orders.sent_on = IF('".$sent_on."'='',orders.sent_on,'".date('Y-m-d',strtotime($sent_on))."'),
				orders.payed_on = IF('".$payed_on."'='',orders.payed_on,'".date('Y-m-d',strtotime($payed_on))."'),
				orders.wishes = '".$wishes."',
				orders.postnum = ".$postnum."
				WHERE
				orders.id = '".$id_arr[0]."'
				AND
				orders.user_num = '".$id_arr[1]."'
				AND
				orders.payment_method = '".$id_arr[2]."'
				");
	
		if($status==3){
			$BL = new Blocks($this->registry,false);
			$mail_nalog = $BL->order_nalog($id_arr,1);
			$BL->order_goods_rate($num,1);
			$mail_discount = $BL->mk_discount($id_arr,1);
			$BL->mail_user_data_change($mail_nalog,$mail_discount,$id_arr);
		}elseif($status==4){
			$BL = new Blocks($this->registry,false);
			$BL->ostatki_order_cancel_notify($num);
		}
	
		if($status==4){//если заказ отменен, смотрим, есть ли в нем товары по остаткам
			Settings::order_cancel($id_arr);
		}elseif($status==3){//если заказ оплачен, удаляем резерв
			Settings::order_apply($id_arr);
		}
	
	}		
}
?>