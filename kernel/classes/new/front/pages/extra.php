<?php
Class Front_Pages_Extra{

	private $registry;
			
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	public function get_extra($alias){
		$classname = 'Front_'.$alias;
		
		if(!class_exists($classname) || !method_exists($classname,'page_extra')) return false;
		
		$CL = new $classname($this->registry);
		return $CL->page_extra();
	}		
}
?>