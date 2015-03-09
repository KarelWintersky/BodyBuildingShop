<?php
Class Adm_Helper_Content{
	
	/*
	 * удаляет ненужный мусор из кода при сохранении контента (страницы, статьи, новости)
	 * */
	public static function delete_junk($content){
		$content = str_replace('&nbsp;</p>','</p>',$content);
		
		return $content;
	}	
	
	/*
	 * заменяет всюду div на p
	 * */
	public static function div_replace($content){
		$content = str_replace('<div','<p',$content);
		$content = str_replace('</div','</p',$content);
		
		return $content;
	}
	
}
?>