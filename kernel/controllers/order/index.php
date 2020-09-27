<?php
Class Controller_Index Extends Controller_Base{
		
    function index($path = NULL) {
    	if(count($path)){ header('Location: /order/'); exit(); }
    	
    	$this->registry->set('noindex',true);
    	$this->registry['template']->set('tpl','front');
    	$this->registry['f_404'] = false;

    	$this->registry['CL_css']->set(array(
    		'order',
    	));
    	$this->registry['CL_js']->set(array(
    			'order',
    			'order/table',
    	));    	  
    	
    	$Front_Order_Data = new Front_Order_Data($this->registry);
    	
    	$Front_Order_Cart = new Front_Order_Cart($this->registry);
    	$Front_Order_Cart->do_vars();
    }

    function go($path = NULL){
    	$Front_Order_Cart_Go = new Front_Order_Cart_Go($this->registry);
    	$Front_Order_Cart_Go->cart_go();
    }
    
    function bill(){
    	$this->registry['f_404'] = false;
    	
    	$Front_Order_Bill = new Front_Order_Bill($this->registry);
    	$Front_Order_Bill->to_screen();
    	exit();
    }
    
    function check(){
    	Front_Order_Steps::check_step(4);
    	
    	$this->registry->set('noindex',true);
    	$this->registry['template']->set('tpl','front');
    	$this->registry['f_404'] = false;
    	$this->registry['template']->set('c','order/check');
    	
    	$this->registry['CL_css']->set(array(
    			'order',
    	));
    	$Front_Order_Data = new Front_Order_Data($this->registry);

    	$Front_Order_Check = new Front_Order_Check($this->registry);
    	$Front_Order_Check->do_vars();    	
    }    

}


?>
