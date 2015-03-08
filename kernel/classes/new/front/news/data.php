<?php
Class Front_News_Data{

	public static function get_type($val,$key = 'alias'){
		$types = array(
				array(
						'id' => 1,
						'name' => 'Новости сайта',
						'alias' => 'news',
						'alias_in_url' => true,
						'paging' => 10,
						),
				array(
						'id' => 2,
						'name' => 'Новости спортивного питания',
						'alias' => 'novosti-sportivnogo-pitania',
						'alias_in_url' => false,
						'paging' => 10,
						),
				);	
		
		foreach($types as $t) if($t[$key]==$val) return $t;
			
		return false;
	}	
	
}
?>