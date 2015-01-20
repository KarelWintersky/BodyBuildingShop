<?php
Class Controller_Index Extends Controller_Base{
		
    function index($path = NULL) {
    	if(count($path)){ header('Location: /order/'); exit(); }
    	
    	$this->registry['template']->set('tpl','front');
    	$this->registry['f_404'] = false;
    	$this->registry['template']->set('c','order/cart');
    			    	        	 					
    }
                
    function delivery(){
    	$this->registry['template']->set('tpl','front');
    	$this->registry['f_404'] = false;
    	$this->registry['template']->set('c','order/delivery');    	
    }
    
    function payment(){
    	$this->registry['template']->set('tpl','front');
    	$this->registry['f_404'] = false;
    	$this->registry['template']->set('c','order/payment');    	
    }
    
    function check(){
    	$this->registry['template']->set('tpl','front');
    	$this->registry['f_404'] = false;
    	$this->registry['template']->set('c','order/check');
    }    
                         
}


?>
