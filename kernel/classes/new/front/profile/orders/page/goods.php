<?php
Class Front_Profile_Orders_Page_Goods Extends Common_Rq{
	
	private $registry;
	
	private $Adm_Orders_Goods;
	
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Adm_Orders_Goods = new Adm_Orders_Goods($this->registry);
	}	
	
	public function print_goods($order){
		$goods = $this->Adm_Orders_Goods->get_goods($order);
				
		$html = array();
		foreach($goods as $g){
			$g['total_price'] = $g['final_price']*$g['amount']; 
			
			$html[] = $this->do_rq('item',$g,true);
		}
		
		return implode('',$html);
	}
		
}
?>