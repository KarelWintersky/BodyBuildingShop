<?php
Class Front_Order_Check Extends Common_Rq{

	private $registry;
	
	private $Front_Order_Crumbs;
			
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Crumbs = new Front_Order_Crumbs($this->registry);
	}	
		
	public function do_vars(){
		$vars = array(
				'crumbs' => $this->Front_Order_Crumbs->do_crumbs(4)
		);
	
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
			
}
?>