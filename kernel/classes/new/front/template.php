<?php
Class Front_Template{

	private $registry;
	
	private $Front_Template_Vars;
	private $Front_Template_Links;
	private $Front_Template_Css;
	private $Front_Template_Js;
	private $Front_Template_Compress;
	private $Front_Template_Blocks;
	private $Front_Template_Menu;
		
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Template_Vars = new Front_Template_Vars($this->registry);
		$this->Front_Template_Links = new Front_Template_Links($this->registry);
		$this->Front_Template_Css = new Front_Template_Css($this->registry);
		$this->Front_Template_Js = new Front_Template_Js($this->registry);
		$this->Front_Template_Compress = new Front_Template_Compress($this->registry);
		$this->Front_Template_Blocks = new Front_Template_Blocks($this->registry);
		$this->Front_Template_Menu = new Front_Template_Menu($this->registry);
	}	
	
	public function do_template($html){
		$this->Front_Template_Menu->do_menu();
		
		$this->Front_Template_Blocks->do_blocks();
		
		$this->Front_Template_Css->go();
		$this->Front_Template_Js->go();
		
		$html = $this->Front_Template_Vars->vars_replace($html);
		$html = $this->Front_Template_Links->do_links($html);
		
		$html = $this->Front_Template_Compress->do_compress($html);
		
		return $html;
	}
	
}
?>