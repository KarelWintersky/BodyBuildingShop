<?php
Class Adm_Avatar_Upload_Prepare{

	public static function prepare_files_array($avatars){
		/*
		 * структурируем данные из массива $_FILES, группируем данные по каждому из передаваемых файлов. Для удобства работы
		 * */	
		$output = array();
		
		foreach($avatars as $type => $arr)
			foreach($arr as $key => $val)
				$output[$key][$type] = $val;
		
		return $output; 
	} 
	
}
?>