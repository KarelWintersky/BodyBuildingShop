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
				'http://new2.bodybuilding-shop.ru',
				'http://www.bodybuilding-shop.ru',
				'http://bodybuilding-shop.ru',
				'http://bodybuilding-shop',
				);

		$is_outer = false;
		if(strpos('http://',$link)!==false){
			$is_outer = true;
			foreach($domains as $d)
				if(strpos($d,$link)!==false)
					$is_outer = false;			
		}
		if($is_outer) return $link;
		
		foreach($domains as $d)
			$link = str_replace($d,'',$link);
				
		return sprintf('%s/%s',
				rtrim(THIS_URL,'/'),
				ltrim($link,'/')
				);
	}
	
	private function do_replace($matches){
		if($matches[1]=='/' || !$matches[1]) return $matches[0];
				
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