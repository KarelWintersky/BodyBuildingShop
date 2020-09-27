<?php
Class Adm_Orders_Save{

	private $registry;

	private $Front_Order_Write_Ostatok;

	public function __construct($registry){
		$this->registry = $registry;

		$this->Front_Order_Write_Ostatok = new Front_Order_Write_Ostatok($this->registry);
	}
			
	public function order_save(){
		foreach($_POST as $key => $val) $$key = (is_array($val)) ? $val : $val;
	
		$arr = explode('/',$num);
		
		mysql_query(sprintf("
				UPDATE
					orders
				SET
					status = '%d',
					sent_on = '%s',
					payed_on = '%s',
					comment = '%s',
					postnum = %s
				WHERE
					id = '%d'
					AND
					user_num = '%d'
					AND
					payment_method = '%s'
				",
				$status,
				($sent_on) ? date('Y-m-d',strtotime($sent_on)) : '',
				($payed_on) ? date('Y-m-d',strtotime($payed_on)) : '',
				mysql_real_escape_string($comment),
				($postnum) ? sprintf("'%s'",$postnum) : "NULL",
				$arr[0],
				$arr[1],
				$arr[2]
				));
	
		if($status==3){
			$BL = new Blocks($this->registry,false);
			$mail_nalog = $BL->order_nalog($arr,1);
			$BL->order_goods_rate($num,1);
			$mail_discount = $BL->mk_discount($arr,1);
			$BL->mail_user_data_change($mail_nalog,$mail_discount,$arr);

			$this->Front_Order_Write_Ostatok->succesfullyRemoveReserve($num);
		}elseif($status==4){
			$this->Front_Order_Write_Ostatok->unhappyRemoveReserve($num);
		}
		
	}		
}
?>