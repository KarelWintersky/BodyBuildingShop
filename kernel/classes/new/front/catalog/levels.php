<?php
Class Front_Catalog_Levels{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	public function do_vars(){
		$vars = array(
				'sort_by' => $this->registry['CL_catalog_sort']->print_options(0)
				);
		
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
	
}
?>