<?php
Class Front_Order_Delivery_Post{
	
	private $registry;
					
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	public function extra_fields(){
		
	}
	
	public function calculate_cost(){
		$index_arr = $this->registry['logic']->get_index_data(trim($this->registry['userdata']['zip_code']));
		
		$weight = $this->registry['overall_weight'];
		/*$boxtype = ($weight<BANDEROL_MAX_WEIGHT && $this->registry['full_cart_arr']['overall_price']<BANDEROL_MAX_PRICE) ? 0 : 1; //0 - banderol, 1 - posylka
		 $boxtype_name = ($boxtype==0) ? 'бандероли'	: 'посылки';	*/
		$post_500s = ceil($weight/500);
		
		if($index_arr){
		
			$main_cost = $index_arr['tarif_pos_basic'];
			$add_cost_ind = $index_arr['tarif_pos_add'];
			$add_cost = $add_cost_ind*($post_500s-1);
		
			$total_cost = $main_cost + $add_cost;
		
			//hard region
		
			$hard_cost_index = $index_arr['tarif_post_avia_pos'] + $index_arr['tarif_avia_pos'];
			$hard_cost = $hard_cost_index*$post_500s;
		
			$this->registry['delivery_cost_array'] = array(
					'total_cost' => $total_cost,
					'hard_cost' => $hard_cost,
					'cost' => $hard_cost+$total_cost,
					'is_spb' => $index_arr['is_spb']
			);
		
			if($hard_cost>0){
				$this->registry['no_nalog'] = true;
			}
		
		}else{
			$this->registry['false_index'] = true;
			$this->registry['no_nalog'] = true;
		
			$this->registry['delivery_cost_array'] = array(
					'total_cost' => 0,
					'hard_cost' => 0,
					'cost' => 0,
					'is_spb' => 0,
			);
		}
		
	}
			
}
?>