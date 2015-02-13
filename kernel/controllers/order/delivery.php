<?php
Class Controller_Delivery Extends Controller_Base{
		
	function delivery($path = NULL){
    	$this->registry['template']->set('tpl','front');
    	$this->registry['f_404'] = false;
    	$this->registry['template']->set('c','order/delivery');

    	$this->registry['CL_css']->set(array(
    			'order',
    	));    	
    	
    	$Front_Order_Data = new Front_Order_Data($this->registry);
    	
    	$Front_Order_Delivery = new Front_Order_Delivery($this->registry);
    	$Front_Order_Delivery->do_vars();	
	}
	
	function write($path = NULL){
		$Front_Order_Delivery_Write = new Front_Order_Delivery_Write($this->registry);
		$Front_Order_Delivery_Write->do_write();
		
		header('Location: /order/payment/');
		exit();
	}
	
    function index($path = NULL) {
    	$this->delivery($path);
    }
                     
}


?>
