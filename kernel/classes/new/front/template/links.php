<?php
Class Front_Template_Links{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	private function to_lowercase($link){
		$link = mb_strtolower($link,'utf-8');
		
		return $link;
	}
	
	private function replace_domain($link){
		$domains = array(
				'new2.bodybuilding-shop.ru',
				'www.bodybuilding-shop.ru',
				'bodybuilding-shop.ru',
				'bodybuilding-shop',
				);
		
		$link = str_replace('http://','',$link);
		
		foreach($domains as $d)
			$link = ltrim($link,$d);
		
		if(!$link  || $link=='/') return THIS_URL;
		
		return sprintf('%s/%s',
				rtrim(THIS_URL,'/'),
				ltrim($link,'/')
				);
	}
	
	private function do_replace($matches){
		$link = $matches[1];
		$link = $this->to_lowercase($link);
		$link = $this->replace_domain($link);
		
		return str_replace(
				$matches[1],
				$link,
				$matches[0]
				);
	}
	
	public function do_links($html){
		$reg = '/<a href=\"([^\"]*)\">.*<\/a>/iU';
		$html = preg_replace_callback(
				$reg,
				array($this,'do_replace'),
				$html
				);
		
		return $html;
	}
		
}
?>