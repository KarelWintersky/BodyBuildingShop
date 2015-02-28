<?php
Class Front_Profile_Orders_Page_New{
	
	/*
	 * расширение информации о заказах, сделанных по новой системе
	 * */
	
	private $registry;
						
	public function __construct($registry){
		$this->registry = $registry;
	}	

	public function do_extend($order){
		//payment_name
		//final_discount
		//print_bill
		
		return $order;
	}
}
?>