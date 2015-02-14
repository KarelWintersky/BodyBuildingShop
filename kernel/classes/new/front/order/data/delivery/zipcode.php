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
					ind = '%s'
					OR
					ind_old = '%s'
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
	
	/*private function zip_code_query($zip_code,$field){
		return "SELECT
		indexes.ind,
		indexes.ind_old,
		indexes.region,
		indexes.city,
		indexes.type_ogr,
		indexes.tarif_pos_basic,
		indexes.tarif_pos_add,
		indexes.tarif_band_basic,
		indexes.tarif_band_add,
		indexes.type_dost,
		indexes.tarif_post_avia_pos,
		indexes.tarif_avia_pos,
		indexes.tarif_post_avia_band,
		indexes.tarif_avia_band
		FROM
		indexes
		WHERE
		indexes.".$field." = '".$zip_code."'
		AND
		DATE(NOW())
		BETWEEN
		DATE(CONCAT_WS('-',YEAR(NOW()),SUBSTRING_INDEX(SUBSTRING_INDEX(indexes.time_ogr,'-',1),'.',-1),SUBSTRING_INDEX(SUBSTRING_INDEX(indexes.time_ogr,'-',1),'.',1)))
		AND
		DATE(CONCAT_WS('-',YEAR(NOW()),SUBSTRING_INDEX(SUBSTRING_INDEX(indexes.time_ogr,'-',-1),'.',-1),SUBSTRING_INDEX(SUBSTRING_INDEX(indexes.time_ogr,'-',-1),'.',1)))";
	}
	
	public function zip_code_find($zip_code,&$flag){
		$qLnk = mysql_query($this->zip_code_query($zip_code,'ind'));
	
		if(mysql_num_rows($qLnk)>0){
			$this->registry['zc_data'] = mysql_fetch_assoc($qLnk);
			$flag = true;
		}else{
	
			$qLnk = mysql_query($this->zip_code_query($zip_code,'ind_old'));
			if(mysql_num_rows($qLnk)>0){
				$zc_data = mysql_fetch_assoc($qLnk);
				$zc_data['in_old_index'] = true;
				$this->registry['zc_data'] = $zc_data;
				$flag = true;
			}else{
				$flag = false;
			}
		}
	
	}
	
	public function zip_code_data($zip_code){
	
		$flag = true;
	
		$delivery_statuses = array(
				0 => 'В настоящий период времени доставка к Вам невозможна',
				1 => 'В настоящий период времени к Вам возможна комбинированная доставка - сначала наземным, затем авиатранспортом',
				2 => 'В настоящий период времени к Вам возможна комбинированная доставка только авиатранспортом',
				3 => 'В настоящий период времени доставка к Вам возможна наземным транспортом',
		);
	
		$ogr_types = array(
				1 => 'Вы проживаете в труднодоступном регионе, в который периодически запрещается прием посылок для пересылки наземным транспортом. Авиа-доставка в Ваш регион ОТСУТСТВУЕТ!',
				2 => 'Вы проживаете в труднодоступном регионе, в который периодически запрещается прием  посылок для пересылки наземным транспортом. Авиа-доставка ЕСТЬ.',
				3 => 'Вы проживаете в труднодоступном регионе, в который доставка почтовых отправлений осуществляется только авиа-транспортом. Поэтому Вы можете делать заказы только ПО ПРЕДОПЛАТЕ или с оплатой через систему WebMoney',
		);
	
		if(!isset($this->registry['zc_data'])){
			$this->zip_code_find($zip_code,$flag);
		}
	
		if($flag){
			$zc_data = $this->registry['zc_data'];
			$zc_data['delivery_status'] = $delivery_statuses[$zc_data['type_dost']];
			$zc_data['ogr_type'] = isset($ogr_types[$zc_data['type_ogr']]) ? $ogr_types[$zc_data['type_ogr']] : '';
		}
	
		require($this->registry['template']->TF.'item/profile/zip_code_data.html');
	
	}	*/
			
}
?>