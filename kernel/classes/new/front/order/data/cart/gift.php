<?php
Class Front_Order_Data_Cart_Gift{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	private function any_gift(){
		return array(
				'name' => 'Подарок на усмотрение администрации',
				'gift' => true,
				);
	}
		
	public function get_data(){
		$barcode = $this->registry['CL_storage']->get_storage('gift');
		if($barcode===false) return false;
		
		return $this->get_gift($barcode);
	}
	
	public function get_gift($barcode){
		//подарок на усмотрение администрации
		if($barcode==='0') return $this->any_gift();
		
		$arr = array(sprintf("'%s'",$barcode));
		$qLnk = Front_Order_Data_Cart_Query::do_query($arr);
		$goods = mysql_fetch_assoc($qLnk);
		if(!$goods) return false;
		
		$goods['gift'] = true;
		
		return $goods;		
	}
		
}
?>