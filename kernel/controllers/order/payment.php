<?php
Class Controller_Payment Extends Controller_Base{
		
	function payment($path = NULL){
		Front_Order_Steps::check_step(3);
		
		$this->registry->set('noindex',true);
    	$this->registry['template']->set('tpl','front');
    	$this->registry['f_404'] = false;
    	$this->registry['template']->set('c','order/payment');

    	$this->registry['CL_css']->set(array(
    			'order',
    	));
    	 
    	$Front_Order_Data = new Front_Order_Data($this->registry);
    	
    	$Front_Order_Payment = new Front_Order_Payment($this->registry);
    	$Front_Order_Payment->do_vars();	
	}
	
	function write($path = NULL){
		Front_Order_Steps::check_step(3);
		
		$Front_Order_Payment_Write = new Front_Order_Payment_Write($this->registry);
		$Front_Order_Payment_Write->do_write();
		
		header('Location: /order/check/');
		exit();
	}
	
    function index($path = NULL) {
    	$this->payment($path);
    }
                     
}


?>
