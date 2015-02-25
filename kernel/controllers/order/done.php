<?php
Class Controller_Done Extends Controller_Base{
		
	function done($path = NULL){
		if(count($path)){
			header('Location: /order/');
			exit();
		}
		
    	$this->registry['template']->set('tpl','front');
    	$this->registry['f_404'] = false;
    	$this->registry['template']->set('c','order/done');

    	$this->registry['CL_css']->set(array(
    			'order',
    	)); 
    	$this->registry['CL_js']->set(array(
    			'order/done',
    	));    	   	
    		
    	$Front_Order_Done = new Front_Order_Done($this->registry);
    	$Front_Order_Done->do_vars();
	}
		
    function index($path = NULL) {
    	$this->done($path);
    }
                     
}


?>
