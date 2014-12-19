<?php
Class Adm_Settings_Sitemap_Txt{

	private $registry;
	
	private $file;
		
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->file = ROOT_PATH.'public_html/sitemap_.txt';
	}
	
	private function mk_links($pages){
		$links = array();
		
		foreach($pages as $first_level_alias => $arr){
			$alias_f = ($first_level_alias!='index')
				? THIS_URL.$first_level_alias.'/'
				: THIS_URL;
			$links[] = $alias_f;
			
			if(!isset($arr['ch'])) continue;
				
			foreach($arr['ch'] as $second_level_alias => $ch_arr){
		
				$alias_s = $alias_f.$second_level_alias.'/';
				$links[] = $alias_s;
				
				if(!isset($ch_arr['ch'])) continue;
		
				foreach($ch_arr['ch'] as $third_level_alias => $th_arr)
					$links[] = $alias_s.$third_level_alias.'/';
			}
		}	

		return $links;
	}
	
	public function do_file($pages){
		$links = $this->mk_links($pages);
		
		$links = implode("\r\n",$links);
		
		file_put_contents($this->file,$links);
	}
	
}
?>