<?php
Class Front_Order_Data_Delivery_Zipcode{
	
	private $registry;
					
	public function __construct($registry){
		$this->registry = $registry;
	}	
				
	private function get_input_value(){
		$courier_zipcode = $this->registry['CL_storage']->get_storage('courier_zipcode');
		if($courier_zipcode) return $courier_zipcode;
		
		return ($this->registry['userdata']) 
			? $this->registry['userdata']['zip_code']
			: false;
	}
	
	public function get_zipcode_data($data){
		
		$zipcode = $this->get_input_value();
		
		$zipcode_data = $this->get_query($zipcode);
		
		$data['zipcode_data'] = array(
				'arr' => $zipcode_data,
				'is_zipcode' => ($zipcode) ? true : false 
				); 
				
		return $data;
	}
	
	public function get_query($zipcode){	
		$zip_code = trim($zipcode);
		if(!$zipcode) return false;
		
		$qLnk = mysql_query(sprintf("
				SELECT
					ind,
					region,
					city,
					type_ogr,
					type_dost,
					tarif_pos_basic,
					tarif_pos_add,
					tarif_band_basic,
					tarif_band_add,
					type_dost,
					tarif_post_avia_pos,
					tarif_avia_pos,
					tarif_post_avia_band,
					tarif_avia_band,
					city,
					IF(city LIKE '%%Санкт-Петербург%%',1,0) AS is_spb
				FROM
					indexes
				WHERE
					(ind = '%s'
					OR
					ind_old = '%s')
					AND
					DATE(NOW())
						BETWEEN
						DATE(CONCAT_WS('-',YEAR(NOW()),SUBSTRING_INDEX(SUBSTRING_INDEX(time_ogr,'-',1),'.',-1),SUBSTRING_INDEX(SUBSTRING_INDEX(time_ogr,'-',1),'.',1)))
						AND
DATE(CONCAT_WS('-',YEAR(NOW()),SUBSTRING_INDEX(SUBSTRING_INDEX(time_ogr,'-',-1),'.',-1),SUBSTRING_INDEX(SUBSTRING_INDEX(time_ogr,'-',-1),'.',1)))
				",
				mysql_real_escape_string($zipcode), 
				mysql_real_escape_string($zipcode)
				));
		$data = mysql_fetch_assoc($qLnk);
			
		return $data;
	}
				
}
?>