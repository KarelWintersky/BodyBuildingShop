<?php
Class Adm_Template{

	private $registry;
	
	private $Adm_Template_Vars;
		
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Template_Vars = new Common_Template_Vars($this->registry);
	}	
	
	public function do_template($html){
		$html = $this->Template_Vars->vars_replace($html);
		
		return $html;
	}
	
}
?>