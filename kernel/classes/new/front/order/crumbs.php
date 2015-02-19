<?php
Class Front_Order_Crumbs Extends Common_Rq{

	private $registry;
			
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	public function do_crumbs($cur){
		$html = array();
		
		$steps = Front_Order_Data_Steps::get_steps();
		
		$i = 1; $do_link = true;
		foreach($steps as $key => $arr){
			$active = ($key==$cur);
			if($active) $do_link = false;
			
			$a = array(
					'name' => $arr[0],
					'num' => $i,
					'active' => ($active) ? 'active' : '',
					'link' => ($do_link) ? sprintf('/%s/',$arr[1]) : false,
					);
			
			$html[] = $this->do_rq('item',$a,true);
			
			$i++;
		}
		
		return $this->do_rq('main',implode('',$html));
	}
		
}
?>