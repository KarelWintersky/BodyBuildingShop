<?php
Class Front_Template_Images{

	private $registry;
	private $domains;
		
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->domains = array(
				'http://new2.bodybuilding-shop.ru',
				'http://www.bodybuilding-shop.ru',
				'http://bodybuilding-shop.ru',
				'http://bodybuilding-shop',
				);
	}	
				
	private function replace_domain($src){
		$is_outer = false;
		if(strpos($src,'http://')!==false || strpos($src,'https://')!==false){
			$is_outer = true;
			foreach($this->domains as $d){
				if(strpos($d,$src)!==false){
					$is_outer = false;		
				}
			}	
		}		
		if($is_outer) return $src;
		
		foreach($this->domains as $d)
			$src = str_replace($d,'',$src);
				
		return sprintf('%s/%s',
				rtrim(THIS_URL,'/'),
				ltrim($src,'/')
				);
	}
			
	private function do_replace($matches){		
		$src = $matches[1];
		$src = $this->replace_domain($src);
		
		return str_replace(
				$matches[1],
				$src,
				$matches[0]
				);
	}
	
	public function do_images($html){		
		$reg = '/src="([^"]*)"/i';
		$html = preg_replace_callback(
				$reg,
				array($this,'do_replace'),
				$html
				);
		
		return $html;
	}
		
}
?>