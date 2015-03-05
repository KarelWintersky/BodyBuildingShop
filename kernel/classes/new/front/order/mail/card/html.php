<?php
Class Front_Order_Mail_Card_Html Extends Common_Rq{
		
	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	private function make_message($order){
		
		return $this->do_rq('tpl',NULL);
	}	
}
?>