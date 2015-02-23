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
				
	private function get_data($num,$skip_user_match){
		$num = explode('/',$num);
		if(count($num)!=3) return false;
		
		return ($num[2]=='A' || $num[2]=='А') 
			? $this->Front_Order_Bill_Account->get_data($num,$skip_user_match)
			: $this->Front_Order_Bill_Cart->get_data($num,$skip_user_match); 
	}
	
	public function print_bill($num,$skip_user_match = false,$to_pdf = false){
		$data = $this->get_data($num,$skip_user_match);
		if(!$data) return false;
		
		$data['to_pdf'] = $to_pdf;
		
		$line = $this->do_rq('line',$data,true);
		
		$a = array(
				'lines' => $line.$line,
				'to_pdf' => $to_pdf 
				);
		
		return $this->do_rq('bill',$a);	
	}
	
	public function to_screen(){
		$num = (isset($_GET['o'])) ? $_GET['o'] : false;
		if($num){
			$html = $this->print_bill($num);
			if($html){
				echo $html;
				exit();
			}
		}
		
		header('Location: /');
		exit();		
	}
	
	public function to_letter($num){
		return $this->print_bill($num,true,true);
	}
			
}
?>