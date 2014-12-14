<?php
Class Front_Template{

	private $registry;
	
	private $Front_Template_Vars;
		
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Template_Vars = new Front_Template_Vars($this->registry);
	}	
	
	public function do_template($html){
		$html = $this->Front_Template_Vars->vars_replace($html);
		
		return $html;
	}
	
}
?>