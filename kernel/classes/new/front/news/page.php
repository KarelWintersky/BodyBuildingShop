<?php
Class Front_News_Page{

	private $registry;
	
	private $Front_News_Prevnext;
		
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_News_Prevnext = new Front_News_Prevnext($this->registry);
	}	
		
	public function news_check($alias,$type){
		$qLnk = mysql_query(sprintf("
				SELECT
					*
				FROM
					news
				WHERE
					type = '%d'
					AND
					published = '1'
					AND
					alias = '%s'
				",
				$type,
				mysql_real_escape_string($alias)
				));
		$news = mysql_fetch_assoc($qLnk);
		if(!$news) return false;
				
		$this->registry['f_404'] = false;
		
		$this->registry['template']->set('c','news/page_');
				
		$this->set_vars($news);
		
		return true;		
	}
	
	private function mk_crumbs($news){
		$type = Front_News_Data::get_type($news['type'],'id');
		
		$this->registry['template']->add2crumbs($type['alias'],$type['name']);
		
		$name = mb_substr($news['name'],0,70,'utf-8');
		if($name!=$news['name']) $name.='...';
		
		$this->registry['template']->add2crumbs($news['alias'],$name);
	}
	
	private function set_vars($news){
		$this->mk_crumbs($news);
		
		$this->registry->set('longtitle',
				($news['longtitle']) ? $news['longtitle'] : $news['name']
				);
		
		$vars = array(
				'date' => Common_Useful_Date::date2node($news['date'],1),
				'name' => $news['name'],
				'content' => $news['content'],
				'prev_next' => $this->Front_News_Prevnext->do_block($news)
				);

		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
	
}
?>