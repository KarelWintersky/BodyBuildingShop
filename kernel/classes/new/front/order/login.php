<?php
Class Front_Order_Login Extends Common_Rq{

	private $registry;
			
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	private function is_authed(){
		/*
		 * проверка, авторизован ли покупатель
		 * */
		
		return ($this->registry['userdata']) ? true : false;
	}
	
	public function do_form(){
		if($this->is_authed()) return $this->do_rq('authed',NULL); 
		
		return $this->do_rq('not_authed',NULL);
	}
	
}
?>