<?php
Class Controller_Cart Extends Controller_Base{
		
	function cart($path = NULL){
		header('Location: /order/');
		exit();
	}
	
    function index($path = NULL) {
    	$this->cart($path);
    }
                     
}


?>
