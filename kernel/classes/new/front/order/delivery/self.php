<?php
Class Front_Order_Delivery_Self Extends Common_Rq{
	
	private $registry;
					
	public function __construct($registry){
		$this->registry = $registry;
	}	

	public function extra_fields(){
		return $this->do_rq('fields',NULL);
	}	
			
}
?>