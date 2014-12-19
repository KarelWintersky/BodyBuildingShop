<?php
Class Controller_Index Extends Controller_Base {
		
    function index($A = NULL) {
    	        	
		$this->registry['template']->set('tpl','front');
		$this->registry->set('route_path', $A);
    	
    	if(count($this->registry['route_path'])==0){
    		$this->registry['CL_css']->set(array(
    				'jcarousel',
    				'accordionImageMenu',
    				));
    		$this->registry['CL_js']->set(array(
    				'lib/jquery.jcarousel.min',
    				'lib/jquery-ui.min',
    				'lib/accordionImageMenu',
    				'mainpage',
    		));    		
    		
    		$this->registry['mp_info'] = $this->registry['template']->get_main_page_content();
    		$this->registry['f_404'] = false;	
    		$this->registry['mainpage'] = true;
    		$this->registry['longtitle'] = $this->registry['mp_info']['seo_title'];
    		$this->registry['h1'] = $this->registry['mp_info']['name'];
    		$this->registry['template']->set('c','main_page');
    		
    		$this->registry['seo_kw'] = $this->registry['mp_info']['seo_kw'];
    		$this->registry['seo_dsc'] = $this->registry['mp_info']['seo_dsc'];
    		
    	}else{
    		$this->registry['router']->path_check();
    	}    	
    	    					
    }
    
    function adm($A = NULL){
    	$this->registry['template']->set('tpl','adm');
    	$this->registry['aias_path'] = $A;
    	
    	if(count($A)==0){
    		$this->registry['f_404'] = false;
    		$this->registry['template']->set('c','index_page');
    	}elseif(count($A)>0 && isset($this->registry['userdata']) && $this->registry['userdata']['type']!=0 && $this->registry['template']->main_part_check($A[0])){
    		$c = new $A[0]($this->registry);
    			$this->registry->set($A[0], $c);    		
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
