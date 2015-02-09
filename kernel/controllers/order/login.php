<?php
Class Controller_Login Extends Controller_Base{
		
	function login($path = NULL){
		if(count($path)){ header('Location: /order/'); exit(); }
		
		$this->registry['CL_css']->set(array(
				'order',
		));		
		
		$this->registry['template']->set('tpl','front');
		$this->registry['f_404'] = false;
		$this->registry['template']->set('c','order/login');		
		
		$Front_Order_Login = new Front_Order_Login($this->registry);
		$Front_Order_Login->do_vars();		
	}
	
    function index($path = NULL) {
    	$this->login($path);
    }
                
    function check(){
    	$Front_Order_Login_Check = new Front_Order_Login_Check($this->registry);
    	$Front_Order_Login_Check->if_authed();
    }
     
}


?>
