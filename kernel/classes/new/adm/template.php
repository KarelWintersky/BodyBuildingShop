<?php
Class Adm_Template{

	private $registry;
			
	public function __construct($registry){
		$this->registry = $registry;
		
		$Adm_Template_Delete = new Adm_Template_Delete($this->registry);
	}	
	
	public function do_template($html){
		$html = $this->registry['CL_template_vars']->vars_replace($html);
		
		return $html;
	}
	
}
?>