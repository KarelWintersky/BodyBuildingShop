<?php
Class Front_Template_Content{

	public static function do_content($alias){
		$folder = ROOT_PATH.'tpl/front/rq/';
		$file = sprintf('%s%s/main.html',$folder,$alias);
		
		if(is_file($file)) require($file);
		else die(sprintf('Не найден файл %s',$file));
	}
		
}
?>