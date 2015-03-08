<?php
Class Front_News_Prevnext Extends Common_Rq{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	private function data_q($sign,$date,$type,$id,$dir){
		$qLnk = mysql_query(sprintf("
				SELECT
					name,
					alias
				FROM
					news
				WHERE
					date %s= '%s'
					AND
					published = 1
					AND
					type = '%d'
					AND
					id <> '%d'
				ORDER BY
					date %s
				LIMIT 1;
				",
				$sign,
				$date,
				$type,
				$id,
				$dir
				));	

		return mysql_fetch_assoc($qLnk);
	}
		
	public function do_block($news){
		$a = array(
				'next' => $this->data_q('>',$news['date'],$news['type'],$news['id'],'ASC'),
				'prev' => $this->data_q('<',$news['date'],$news['type'],$news['id'],'DESC'),
				'news_alias' => ($news['type']==1) ? 'news/' : ''
		);

		return $this->do_rq('storage',$a);
	}
}
?>