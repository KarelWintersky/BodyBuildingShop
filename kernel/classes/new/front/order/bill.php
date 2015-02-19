<?php
Class Front_Order_Bill Extends Common_Rq{

	private $registry;
	
	private $Front_Order_Bill_Cart;
	private $Front_Order_Bill_Account;
				
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Bill_Cart = new Front_Order_Bill_Cart($this->registry);
		$this->Front_Order_Bill_Account = new Front_Order_Bill_Account($this->registry);
	}	
				
	private function get_data(){
		$num = (isset($_GET['o'])) ? $_GET['o'] : false;
		if(!$num) return false;
		
		$num = explode('/',$num);
		if(count($num)!=3) return false;
		
		return ($num[2]=='A' || $num[2]=='А') 
			? $this->Front_Order_Bill_Account->get_data($num)
			: $this->Front_Order_Bill_Cart->get_data($num); 
	}
	
	public function print_bill(){
		$data = $this->get_data();
		if(!$data){
			header('Location: /');
			exit();
		}

		$line = $this->do_rq('screen',$data,true);
		$line.=$line;
		
		echo $this->do_rq('screen',$line);	
	}
			
}
?>