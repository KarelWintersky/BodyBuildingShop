<?php
Class Front_Profile_Orders_Page_Old{
	
	/*
	 * расширение информации о заказах, сделанных по старой системе
	 * */
	
	private $registry;
						
	public function __construct($registry){
		$this->registry = $registry;
	}	

	public function do_extend($order){
		
		//payment_name
		//final_discount
		//print_bill
		
		if($orderdata['payment_method']=='Н' || $orderdata['payment_method']=='H'){
			$orderdata['payment_type_text'] = 'наложенным платежом';
		}elseif($orderdata['payment_method']=='W'){
			$orderdata['payment_type_text'] = 'электронными деньгами';
		}elseif($orderdata['payment_method']=='П'){
			if($orderdata['by_card']==1){
				$orderdata['payment_type_text'] = 'банковской картой или через платежные системы';
			}elseif($orderdata['pay2courier']==1){
				$orderdata['payment_type_text'] = 'курьеру';
			}elseif($orderdata['from_account']>0){
				$orderdata['payment_type_text'] = 'с личного счета';
			}else{
				$orderdata['payment_type_text'] = 'предоплата';
			}
		
		}		
		
		return $order;
	}
}
?>