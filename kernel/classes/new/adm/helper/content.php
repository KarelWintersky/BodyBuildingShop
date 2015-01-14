<?php
Class Adm_Helper_Content{
	
	/*
	 * удаляет ненужный мусор из кода при сохранении контента (страницы, статьи, новости)
	 * */
	public static function delete_junk($content){
		$content = str_replace('&nbsp;</p>','</p>',$content);
		
		return $content;
	}	

	
}
?>