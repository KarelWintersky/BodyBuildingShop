<?php
Class Front_Profile_Zipcode{
	
	private $registry;
					
	public function __construct($registry){
		$this->registry = $registry;
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
	
	private function zip_code_query($zip_code,$field){
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
	
	}
			
}
?>