<?php
Class Adm_Orders_Search Extends Common_Rq{

	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}
		
	public function do_block(){
		
		$a = array(
				'num' => (isset($_GET['num'])) ? htmlspecialchars($_GET['num']) : '',
				'statuses' => Adm_Orders_Helper::statuses_options(
						(isset($_GET['status'])) ? $_GET['status'] : false,
						true
						),
				'date_from' => (isset($_GET['date_from'])) ? $_GET['date_from'] : '',
				'date_to' => (isset($_GET['date_to'])) ? $_GET['date_to'] : '',
				);
		
		return $this->do_rq('search',$a);
	}	
}
?>