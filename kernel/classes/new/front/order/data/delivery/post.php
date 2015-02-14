<?php
Class Front_Order_Data_Delivery_Post{

	public function __construct($registry){
		$this->registry = $registry;
	}
	
	public function calculate_costs($data){
		$zipcode_data = ($this->registry['userdata'])
			? Front_Order_Data_Delivery_Zipcode::get_zipcode_data(
					$this->registry['userdata']['zip_code']
			)
			: false;
	
		if($zipcode_data){
	
			$post_500s = ceil($data['weight']/500);
			
			$main_cost = $zipcode_data['tarif_pos_basic'];
			$add_cost_ind = $zipcode_data['tarif_pos_add'];
			$add_cost = $add_cost_ind*($post_500s-1);
	
			$total_cost = $main_cost + $add_cost;
			$total_cost = ceil($total_cost);
	
			//труднодоступный регион
	
			$hard_cost_index = $zipcode_data['tarif_post_avia_pos'] + $zipcode_data['tarif_avia_pos'];
			$hard_cost = $hard_cost_index*$post_500s;
			$hard_cost = ceil($hard_cost);
	
			$output = array(
				'total_cost' => $total_cost,
				'hard_cost' => $hard_cost,
				'cost' => $hard_cost+$total_cost,
				'is_spb' => $zipcode_data['is_spb'],
				'no_nalog' => ($hard_cost>0),
				'no_zip_code' => false
			);
	
		}else{	
			$output = array(
				'total_cost' => false,
				'hard_cost' => false,
				'cost' => false,
				'is_spb' => false,
				'no_nalog' => true,
				'no_zip_code' => ($this->registry['userdata'] && $this->registry['userdata']['zip_code']) ? false : true	
			);
		}
	
		$data['costs']['post'] = $output;
		
		return $data;
	}	
}
?>