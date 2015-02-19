<?php
Class Front_Template_Content{

	public static function do_content($alias){
		
		$folder = ROOT_PATH.'tpl/front/rq/';
		$file = sprintf('%s%s%s',
				$folder,
				str_replace('_','',$alias),
				(strpos($alias,'_')===false) ? '/main.html' : '.html'
				);
		
		if(is_file($file)) require($file);
		else die(sprintf('Не найден файл %s',$file));
	}
		
}
?>