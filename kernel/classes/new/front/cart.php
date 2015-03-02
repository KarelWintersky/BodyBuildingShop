<?php
Class Front_Cart Extends Common_Rq{

	private $registry;
	
	private $Front_Order_Data_Cart_String;
		
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Data_Cart_String = new Front_Order_Data_Cart_String($this->registry);
	}	
		
	public function head_cart(){
		$data = $this->get_data();
				
		$a = array(
				'amount' => ($data) ? $data['amount'] : 0,
				'sum' => ($data) ? Common_Useful::price2read($data['sum']) : 0,
				'authed' => ($this->registry['userdata']),
				'account' => ($this->registry['userdata']) 
					? Common_Useful::price2read($this->registry['userdata']['my_account']) 
					: false,
				'class' => (!$this->registry['userdata']) ? 'to_middle' : ''
				);
		
		return $this->do_rq('cart',$a);
	}
	
	private function get_data(){
		$cart = $this->Front_Order_Data_Cart_String->get_cart_from_string();
		if(!$cart || !count($cart)) return false;
		
		$amounts = array(); $amount = 0; $sum = 0;
		$barcodes = array();
		foreach($cart as $g){
			$barcodes[] = sprintf("'%s'",$g['barcode']);;
			$amounts[$g['barcode']] = $g['amount'];
			
			$amount+=$g['amount'];
		}
		
		$qLnk = mysql_query(sprintf("
				SELECT
					goods_barcodes.barcode,
					goods_barcodes.price,
					goods.personal_discount + %d AS discount
				FROM
					goods_barcodes
				INNER JOIN goods ON goods.id = goods_barcodes.goods_id 
				WHERE
					goods_barcodes.barcode IN (%s)
				",
				OVERALL_DISCOUNT,
				implode(",",$barcodes)
				));
		while($g = mysql_fetch_assoc($qLnk)){
			$price = round($g['price'] - $g['price']*$g['discount']/100);
						
			$sum+= $price*$amounts[$g['barcode']];
		} 
				
		$user_discount = ($this->registry['userdata']) ? $this->registry['userdata']['personal_discount'] : 0;
		
		$sum = $sum - floor($sum*$user_discount/100);
		
		return array(
				'amount' => $amount,
				'sum' => $sum
				);		
	}
		
}
?>