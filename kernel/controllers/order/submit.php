<?php
Class Controller_Submit Extends Controller_Base{
		
	function submit($path = NULL){
		die('Запись заказа в базу. Теперь можно вернуться назад.');	
	}
	
    function index($path = NULL) {
    	$this->submit($path);
    }
                     
}


?>
