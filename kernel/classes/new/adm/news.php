<?php
Class Adm_News Extends Common_Rq{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}
		
	public function news_check($id){					
		if($id=='n'){
			$this->set_vars(
					false,
					(isset($_GET['type'])) ? $_GET['type'] : 1
					);
			
			return true;
		}
		
		$qLnk = mysql_query(sprintf("
				SELECT 
					* 
				FROM 
					news 
				WHERE 
					id = '%d';",
				$id
				));
		$news = mysql_fetch_assoc($qLnk);
		if(!$news) return false;
		
		$this->set_vars(
				$news,
				$news['type']
				);
		
		return true;		
	}	
	
	private function type_block($type){
		$data = Adm_News_Types::get_types($type);
		
		$a = array(
				'name' => mb_strtolower($data[0],'utf-8'),
				'color' => $data[1],
				);
		
		return $this->do_rq('type',$a);
	}
	
	private function set_vars($news,$type){
		$vars = array(
				'h1' => ($news) ? $news['name'] : 'Добавить новость',
				'type' => $this->type_block($type),
				'type_id' => ($news) 
					? $news['type'] 
					: ((isset($_GET['type'])) ? $_GET['type'] : 1),
				'id' => ($news) ? $news['id'] : 0,
				'name' => ($news) ? htmlspecialchars($news['name']) : '',
				'alias' => ($news) ? $news['alias'] : '',
				'date' => ($news) ? date('d.m.Y',strtotime($news['date'])) : date('d.m.Y'),
				'content' => ($news) ? $news['content'] : '',
				'introtext' => ($news) ? $news['introtext'] : '',
				'published' => ($news && $news['published']) ? 'checked' : '',
				'rss' => ($news && $news['rss']) ? 'checked' : '',
				'submit' => ($news) ? 'Сохранить' : 'Добавить',
				);
		
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
	
}
?>