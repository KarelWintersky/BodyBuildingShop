<?php
Class Front_Order_Cart{

	private $registry;
	
	private $Front_Order_Cart_Table;
	private $Front_Order_Login;
		
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Cart_Table = new Front_Order_Cart_Table($this->registry);
		$this->Front_Order_Login = new Front_Order_Login($this->registry);
	}	
		
	public function do_vars(){
		$vars = array(
				'table' => $this->Front_Order_Cart_Table->do_table(),				
				'login' => $this->Front_Order_Login->do_form(),				
				);
		
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
	
}
?>