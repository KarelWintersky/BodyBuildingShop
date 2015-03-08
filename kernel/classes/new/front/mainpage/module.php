<?php
Class Front_Mainpage_Module Extends Common_Rq{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	public function do_module(){
		return $this->do_rq('storage',NULL);
	}
	
	public function F_main_page_module_items(){
	
		$file = ROOT_PATH.'files/module.txt';
		if(is_file($file)){
			$lines = preg_split("/[\n\r]+/s", file_get_contents($file));
			foreach($lines as $l){
				$arr = explode('::',$l);
				if(count($arr)==3){
					$a['filename'] = $arr[0];
					$a['link'] = $arr[1];
					$a['alt'] = htmlspecialchars($arr[2]);
					$this->item_rq('module_item',$a);
				}
			}
		}
	
	}	
	
}
?>