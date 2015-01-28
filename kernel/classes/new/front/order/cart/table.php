<?php
Class Front_Order_Cart_Table Extends Common_Rq{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	private function print_lines($goods){
		$html = array();
		
		foreach($goods as $g){
			$html[] = $this->do_rq('line',$g,true);
		}
		
		return implode('',$html);
	}
	
	public function do_table(){
		$data = $this->registry['CL_data']->get_data();		
		
		$a = array(
				'lines' => $this->print_lines($data['goods'])
				);
		
		return $this->do_rq('table',$a);
	}
	
}
?>