<?php
Class Front_Mainpage_News Extends Common_Rq{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	private function get_data($type,$limit){
		$news = array();
		$qLnk = mysql_query(sprintf("
				SELECT
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
				LIMIT %d;
				",
				$type, $limit
				));

		while($n = mysql_fetch_assoc($qLnk)) $news[] = $n;
		
		return $news;
	}
	
	public function nutrition_news(){
		$news = $this->get_data(2,2);
		
		$html = array();
		foreach($news as $n)
			$html[] = $this->do_rq('nutrition',$n,true);
		
		$type = Front_News_Data::get_type(2,'id');
		
		$a = array(
				'link' => sprintf('/%s/',$type['alias']),
				'list' => implode('',$html) 
				);
		
		return $this->do_rq('nutrition',$a);
	}
	
	public function site_news(){
		$news = $this->get_data(1,3);
		
		$html = array();
		foreach($news as $n)
			$html[] = $this->do_rq('site',$n,true);
		
		return $this->do_rq('site',
				implode('',$html)
				);
	}	
}
?>