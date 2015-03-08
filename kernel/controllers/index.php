<?php
Class Controller_Index Extends Controller_Base {
		
    function index($A = NULL) {
    	        	
		$this->registry['template']->set('tpl','front');
		$this->registry->set('route_path', $A);
    	
    	if(count($this->registry['route_path'])==0){
    		$this->registry['CL_css']->set(array(
    				'mainpage',
    				));
    		$this->registry['CL_js']->set(array(
    				'lib/jquery-ui.min',
    				'mainpage',
    		));    		
    		
    		$this->registry['f_404'] = false;	
    		$this->registry['mainpage'] = true;

    		$this->registry['template']->set('c','mainpage');
    		
    		$Front_Mainpage = new Front_Mainpage($this->registry);
    		$Front_Mainpage->set_vars();
    		
    	}else{
    		$this->registry['router']->path_check();
    	}    	
    	    					
    }
        
    function doer($A = NULL){
    	if(count($A)==0){
    		$this->registry['f_404'] = false;
    		$this->registry['template']->set('no_tpl',true);
    		$D = new Doer($this->registry);
    	}    	
    }
                     
}


?>
