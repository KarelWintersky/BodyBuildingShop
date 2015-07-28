<?php
Class Controller_Card Extends Controller_Base{
		
	function result($path = NULL){			
		$Front_Order_Payment_Card_Result = new Front_Order_Payment_Card_Result($this->registry);
		$Front_Order_Payment_Card_Result->do_result($path);
		exit();
	}	
	
	function error(){
		$this->registry->set('noindex',true);
		$this->registry['template']->set('tpl','front');
		$this->registry['f_404'] = false;
		$this->registry['template']->set('c','order/card/error_');
		
		$this->registry->set('longtitle','Ошибка при оплате заказа');

		$this->registry['CL_css']->set(array(
				'order',
		));		
	}
		
	function prepare($path = NULL){
		$this->registry->set('noindex',true);
		$this->registry['template']->set('tpl','front');
		$this->registry['f_404'] = false;
		$this->registry['template']->set('c','order/card/prepare_');
		
		$this->registry['CL_css']->set(array(
				'order',
		));		
		
		$Front_Order_Payment_Card = new Front_Order_Payment_Card($this->registry);
		$Front_Order_Payment_Card->do_prepare();
	}
	
	function card($path = NULL){}
	
    function index($path = NULL){}
                     
}


?>
