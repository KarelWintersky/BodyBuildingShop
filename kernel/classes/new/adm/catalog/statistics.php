<?php
Class Adm_Catalog_Statistics Extends Common_Rq{

	private $registry;
	
	private $Adm_Catalog_Statistics_Data;
			
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Adm_Catalog_Statistics_Data = new Adm_Catalog_Statistics_Data($this->registry);
	}

	public function route_check(){
		return (isset($_GET['orders']));
	}
		
	public function template_vars(){
		$vars = array(
			'head' => $this->do_rq('head',$this->registry['good']),
			'list' => $this->print_list()
		);
		
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);		
	}
	
	private function print_list(){
		$data = $this->Adm_Catalog_Statistics_Data->get_data();
			
		$html = array(); $month_etal = false;
		foreach($data as $o){
			$o['month_change'] = ($month_etal != date('m.Y',strtotime($o['made_on'])));
			$o['status_name'] = Adm_Orders_Helper::get_statuses($o['status']);
			
			$html[] = $this->do_rq('line',$o,true);
			
			$month_etal = date('m.Y',strtotime($o['made_on']));
		}
	
		return implode('',$html);
	}
	
	/*private function goods_in_orders_pagination(){
	
		$PAGING = 50;
	
		$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$offset = $PAGING*($page-1);
	
		return "LIMIT ".$offset.", ".$PAGING;
	}
	
	public function goods_in_orders_nav($total_amount){
	
		$url = $_SERVER['SCRIPT_URL'];
	
		$PAGING = 50;
		$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
	
		$prev = $page - 1;
		$prev_url = ($prev==1) ? $url : $url.'?page='.$prev;
		$next = $page + 1;
	
		if($prev>0) echo '<li><a href="'.$prev_url.'">« Пред</a></li>';
		echo '<li><a href="'.$url.'?page='.$next.'">След »</a></li>';
	}	*/
}
?>