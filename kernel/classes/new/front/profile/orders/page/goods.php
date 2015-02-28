<?php
Class Front_Profile_Orders_Goods Extends Common_Rq{
	
	private $registry;
	
	private $Adm_Orders_Goods;
	
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Adm_Orders_Goods = new Adm_Orders_Goods($this->registry);
	}	
	
	public function print_goods($order_num){
		$goods = $this->Adm_Orders_Goods->get_goods($order_num);
		
		$html = array();
		foreach($goods as $g){
			$html[] = $this->do_rq('item',$g,true);
		}
		
		return implode('',$html);
	}
		
}
?>