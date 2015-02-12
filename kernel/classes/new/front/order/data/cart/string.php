<?php
Class Front_Order_Data_Cart_String{

	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
		$this->registry->set('CL_cart_string',$this);
	}	
		
	public function get_cart_from_string(){
		$cart = array();
		$string = (isset($_COOKIE['thecart'])) ? $_COOKIE['thecart'] : false;
				
		if(!$string) return false; 
		
		$string = explode('|',$string);
				
		foreach($string as $line){
			$line = explode(':',$line);
			if(count($line)!=3 && count($line)!=4) continue;
					
			$color = (isset($line[3])) ? $line[3] : false;
			
			$key = $line[0].':'.$color;
			
			$cart[$key] = array(
				'barcode' => $line[0], 	
				'packing' => $line[1],
				'amount' => $line[2],
				'color' => $color
			);
		}
		
		return (count($cart)) ? $cart : false;		
	}
	
}
?>