<?php
Class Controller_Submit Extends Controller_Base{
		
	function submit($path = NULL){
		$Front_Order_Write = new Front_Order_Write($this->registry);
		$Front_Order_Write->do_write();
	}
	
    function index($path = NULL) {
    	$this->submit($path);
    }
                     
}


?>
