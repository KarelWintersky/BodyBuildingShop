<?php
Class Front_Template{

	private $registry;
	
	private $Front_Template_Vars;
	private $Front_Template_Links;
		
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Template_Vars = new Front_Template_Vars($this->registry);
		$this->Front_Template_Links = new Front_Template_Links($this->registry);
	}	
	
	public function do_template($html){
		$html = $this->Front_Template_Vars->vars_replace($html);
		$html = $this->Front_Template_Links->do_links($html);
		
		return $html;
	}
	
}
?>