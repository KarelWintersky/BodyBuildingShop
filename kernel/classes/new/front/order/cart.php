<?php
Class Front_Order_Cart{

	private $registry;
	
	private $Front_Order_Cart_Table;
		
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Cart_Table = new Front_Order_Cart_Table($this->registry);
	}	
		
	public function do_vars(){
		$vars = array(
				'table' => $this->Front_Order_Cart_Table->do_table(),				
				'login' => '',				
				);
		
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
	
}
?>