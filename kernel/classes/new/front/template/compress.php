<?php
Class Front_Template_Compress{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
	
	public function do_compress($html){
		if(!OPTIMISE_FRONTEND) return $html;
		
		$search = array(
			'/\>[^\S ]+/s',  // strip whitespaces after tags, except space
			'/[^\S ]+\</s',  // strip whitespaces before tags, except space
			'/(\s)+/s'       // shorten multiple whitespace sequences
		);
		
		$replace = array(
			'>',
			'<',
			'\\1'
		);
		
		$html = preg_replace($search, $replace, $html);
		
		return $html;
	}
			
		
}
?>