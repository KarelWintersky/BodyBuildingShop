<?php
Class Controller_Index Extends Controller_Base{
		
    function index($path = NULL) {
    	if(count($path)){ header('Location: /order/'); exit(); }
    	
    	$this->registry['template']->set('tpl','front');
    	$this->registry['f_404'] = false;
    	$this->registry['template']->set('c','order/cart');

    	$this->registry['CL_css']->set(array(
    		'order',
    	));  

    	$Front_Order_Data = new Front_Order_Data($this->registry);
    	
    	$Front_Order_Cart = new Front_Order_Cart($this->registry);
    	$Front_Order_Cart->do_vars();
    }
                
    function delivery(){
    	$this->registry['template']->set('tpl','front');
    	$this->registry['f_404'] = false;
    	$this->registry['template']->set('c','order/delivery');

    	$this->registry['CL_css']->set(array(
    			'order',
    	));    	
    	
    	$Front_Order_Delivery = new Front_Order_Delivery($this->registry);
    	$Front_Order_Delivery->do_vars();    	
    }
    
    function payment(){
    	$this->registry['template']->set('tpl','front');
    	$this->registry['f_404'] = false;
    	$this->registry['template']->set('c','order/payment');

    	$this->registry['CL_css']->set(array(
    			'order',
    	));
    	 
    	$Front_Order_Payment = new Front_Order_Payment($this->registry);
    	$Front_Order_Payment->do_vars();    	
    }
    
    function check(){
    	$this->registry['template']->set('tpl','front');
    	$this->registry['f_404'] = false;
    	$this->registry['template']->set('c','order/check');
    	
    	$this->registry['CL_css']->set(array(
    			'order',
    	));
    	
    	$Front_Order_Check = new Front_Order_Check($this->registry);
    	$Front_Order_Check->do_vars();    	
    }    

}


?>
