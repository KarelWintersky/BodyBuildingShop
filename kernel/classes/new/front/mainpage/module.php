<?php
Class Front_Mainpage_Module Extends Common_Rq{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	public function do_module(){
		
		$this->registry['CL_js']->set(array(
			'lib/jquery.nivo.slider.min',
		));		
		
		return $this->do_rq('storage',
				$this->get_slides()
				);
	}
	
	private function get_slides(){
		$file = ROOT_PATH.'files/module.txt';
		if(!is_file($file)) return false;
			
		$lines = preg_split("/[\n\r]+/s", file_get_contents($file));
		
		$html = array(); $first_img = false;
		foreach($lines as $l){
			$arr = explode('::',$l);
			if(count($arr)!=3) continue;
			
			$arr[0] = sprintf('/data/foto/module/%s',$arr[0]);
			
			if(!$first_img) $first_img = $arr[0]; 
			
			$html[] = $this->do_rq('item',$arr,true);
		}	
		
		return array(
				'slides' => implode('',$html),
				'bg' => $first_img
				);
		
		return implode('',$html);
	}	
	
}
?>