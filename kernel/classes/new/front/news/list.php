<?php
Class Front_News_List Extends Common_Rq{

	private $registry;
	
	private $Adm_Pagination;
			
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	public function list_check($alias){
		$type = Front_News_Data::get_type($alias);
		if(!$type) return false;
		
		$this->registry['f_404'] = false;
		
		$this->registry['template']->set('c','news/list_');
		$this->registry['template']->add2crumbs($type['alias'],$type['name']);		
		
		$this->Adm_Pagination = new Adm_Pagination($this->registry,
				sprintf('/%s/',$type['alias'])
				);
		
		$this->set_vars($type);
		
		return true;
	}	
	
	private function do_longtitle($title){
		if(isset($_GET['page'])) $title.=', страница '.$_GET['page'];
		
		return $title;
	}
	
	private function mk_limit($paging){
		$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$offset = $paging*($page-1);
				
		return "LIMIT ".$offset.", ".$paging;		
	}
	
	private function print_list($type){
		$qLnk = mysql_query(sprintf("
				SELECT SQL_CALC_FOUND_ROWS
					name,
					date,
					introtext,
					alias
				FROM
					news
				WHERE
					published = 1
					AND
					type = '%d'
				ORDER BY
					date DESC
				%s
				",
				$type['id'],
				$this->mk_limit($type['paging'])
				));
		
		$this->Adm_Pagination->set_params(
				mysql_result(mysql_query("SELECT FOUND_ROWS();"),0),
				$type['paging']
		);
						
		$news = array();
		while($n = mysql_fetch_assoc($qLnk)) $news[] = $n;
			
		$html = array();
		foreach($news as $n){
			$n['date'] = Common_Useful_Date::date2node($n['date'],1);
			$n['url'] = ($type['alias_in_url'])
				? sprintf('/%s/%s/',
						$type['alias'],
						$n['alias']
						)
				: sprintf('/%s/',$n['alias']);
			
			$html[] = $this->do_rq('list',$n,true);
		}
		
		return implode('',$html);
	}
	
	private function set_vars($type){
		$this->registry->set('longtitle',
				$this->do_longtitle($type['name'])
				);
		
		$vars = array(
			'h1' => $type['name'],
			'list' => $this->print_list($type),
			'paging' => $this->Adm_Pagination->print_paging()
		);
	
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}	
}
?>