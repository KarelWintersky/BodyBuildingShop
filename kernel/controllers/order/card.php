<?php
Class Controller_Card Extends Controller_Base{
		
	/*function delivery($path = NULL){
		Front_Order_Steps::check_step(2);
		
    	$this->registry['CL_css']->set(array(
    			'order',
    	));    	
    	$this->registry['CL_js']->set(array(
    			'order/delivery',
    	));    	
    	
    	$Front_Order_Data = new Front_Order_Data($this->registry);
    	
    	$Front_Order_Delivery = new Front_Order_Delivery($this->registry);
    	$Front_Order_Delivery->do_vars();	
	}*/

	function result($path = NULL){			
		$Front_Order_Payment_Card_Result = new Front_Order_Payment_Card_Result($this->registry);
		$Front_Order_Payment_Card_Result->do_result($path);
		exit();
	}	
	
	function error(){
		$this->registry['template']->set('tpl','front');
		$this->registry['f_404'] = false;
		$this->registry['template']->set('c','order/card/error_');

		$this->registry['CL_css']->set(array(
				'order',
		));		
	}
	
	function done(){
		$this->registry['template']->set('tpl','front');
		$this->registry['f_404'] = false;
		$this->registry['template']->set('c','order/card/done_');
		
		$this->registry['CL_css']->set(array(
				'order',
		));		
		
		$Front_Order_Payment_Card_Done = new Front_Order_Payment_Card_Done($this->registry);
		$Front_Order_Payment_Card_Done->do_page();
	}
	
	function prepare($path = NULL){
		$this->registry['template']->set('tpl','front');
		$this->registry['f_404'] = false;
		$this->registry['template']->set('c','order/card/prepare_');
		
		$this->registry['CL_css']->set(array(
				'order',
		));		
		
		$Front_Order_Payment_Card = new Front_Order_Payment_Card($this->registry);
		$Front_Order_Payment_Card->do_prepare();
	}
	
    function index($path = NULL) {
    	//$this->delivery($path);
    }
                     
}


?>
