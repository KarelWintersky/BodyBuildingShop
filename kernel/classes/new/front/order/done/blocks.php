<?php
Class Front_Order_Done_Blocks Extends Common_Rq{

	/*
	 * типовые блоки для последнего шага заказа и email-уведомлений
	 * */
	
	private $registry;
	private $blocks;
					
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->blocks = array(
				'address',
				'requisites',
				'webmoney',
				'post',
				'post2',
				'courier',
				'courier2',
				'self',
				'self2',
				'prepay',
				);
	}	
				
	public function get_blocks($order){
		$output = array();
		
		foreach($this->blocks as $alias)
			$output[$alias] = $this->do_rq($alias,$order);
		
		return $output;
	}				
}
?>