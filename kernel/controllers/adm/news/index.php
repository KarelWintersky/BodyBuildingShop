<?php
Class Controller_Index Extends Controller_Base{
		
    function index($path = NULL) {
    	
    	$this->registry['template']->set('tpl','adm');
    	    	
    	$Adm_News = new Adm_News($this->registry);
    	$Adm_News_List = new Adm_News_List($this->registry);
    	
  		if(!count($path)){
	        $this->registry['f_404'] = false;
	        	
	        $Adm_News_List->set_vars();
	        	
	        $this->registry['template']->set('c','news/list_');
	     }elseif(count($path)==1 && $Adm_News->news_check($path[0])){
	        $this->registry['f_404'] = false;
	        
	        $this->registry['template']->set('c','news/news_');
	     }
    	
    }

}


?>
