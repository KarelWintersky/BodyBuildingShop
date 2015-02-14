<?php
Class Front_Order_Data_Delivery_Courier{

	public function __construct($registry){
		$this->registry = $registry;
	}
	
	public function calculate_costs($data){
		if(!$data['costs']['post']['is_spb']) 
			$output = array(
					'sum' => false,
					'is_spb' => false,
					'no_zip_code' => $data['costs']['post']['no_zip_code'] 
					);
		else{
			$costs = ($data['sum']>=FREE_DELIVERY_SUM) ? 0 : COURIER_SPB_COST;
			
			$output = array(
					'sum' => $costs,
					'is_spb' => true,
					'no_zip_code' => $data['costs']['post']['no_zip_code']
			);			
		}
		
		$data['costs']['courier'] = $output;
		
		return $data;
	}	
}
?>