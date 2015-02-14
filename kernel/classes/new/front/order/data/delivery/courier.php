<?php
Class Front_Order_Data_Delivery_Courier{

	public function __construct($registry){
		$this->registry = $registry;
	}
		
	private function is_spb($data){
		$courier_city = $this->registry['CL_storage']->get_storage('courier_city');
		if($courier_city){
			$courier_city = trim($courier_city);
			$courier_city = mb_strtolower($courier_city,'utf-8');
			
			if(strpos($courier_city,'петербург')!==false) return true;
			if($courier_city=='спб') return true;
			if($courier_city=='питер') return true;
		}
		
		return $data['zipcode_data']['arr']['is_spb'];
	}
	
	public function calculate_costs($data){
		$is_spb = $this->is_spb($data);
		
		if(!$is_spb){ 
			$output = array(
					'sum' => false,
					'is_spb' => false,
					'is_zipcode' => $data['zipcode_data']['is_zipcode'] 
					);
		}else{
			$costs = ($data['sum']>=FREE_DELIVERY_SUM) ? 0 : COURIER_SPB_COST;
			
			$output = array(
					'sum' => $costs,
					'is_spb' => true,
					'is_zipcode' => $data['zipcode_data']['is_zipcode']
			);			
		}
		
		$data['costs']['courier'] = $output;
		
		return $data;
	}	
}
?>