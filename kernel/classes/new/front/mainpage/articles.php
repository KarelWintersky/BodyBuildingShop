<?php
Class Front_Mainpage_Articles Extends Common_Rq{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	

	private function get_data(){
		$articles = array();
		$qLnk = mysql_query("
				SELECT
					main_h2,
					alias,
					img_alt,
					introtext,
					avatar
				FROM
					articles
				WHERE
					published = 1
					AND
					id IN (1,2,3)
				LIMIT 3;
				");
		while($a = mysql_fetch_assoc($qLnk)) $articles[] = $a;
			
		return $articles;
	}
	
	public function do_articles(){
		$articles = $this->get_data();
		
		$html = array();
		
		foreach($articles as $a)
			$html[] = $this->do_rq('item',$a,true);
		
		return implode('',$html);
	}
		
}
?>