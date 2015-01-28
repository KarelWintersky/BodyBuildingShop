<?php
Class Front_Order_Data_Cart_String{

	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	public function get_cart_from_string(){
		$cart = array();
		$string = (isset($_COOKIE['thecart'])) ? $_COOKIE['thecart'] : false;
		
		if(!$string) return false; 
		
		$string = explode('|',$string);
				
		foreach($string as $line){
			$line = explode(':',$line);
			if(count($line)!=3 && count($line)!=4) continue;
					
			$cart[$line[0]] = array(
				'packing' => $line[1],
				'amount' => $line[2],
				'color' => (isset($line[3])) ? $line[3] : false
			);
		}
		
		return (count($cart)) ? $cart : false;		
	}
	
}
?>