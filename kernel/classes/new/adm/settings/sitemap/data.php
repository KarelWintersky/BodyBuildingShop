<?php
Class Adm_Settings_Sitemap_Data{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}
	
	private function get_goods(){
		$data = array();
		
		$qLnk = mysql_query("
				SELECT
					goods.alias,
					goods.modified,
					levels.modified AS level_modified,
					levels.alias AS level_alias,
					parent_tbl.alias AS parent_level_alias,
					parent_tbl.modified AS parent_level_modified
				FROM
					goods
				LEFT OUTER JOIN levels ON levels.id = goods.level_id
				LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
				WHERE
					(goods.parent_barcode = '' OR goods.parent_barcode = 0)
					AND
					goods.alias <> ''
					AND
					levels.alias <> ''
					AND
					parent_tbl.alias <> ''
				ORDER BY
					parent_tbl.sort ASC,
					levels.sort ASC,
					goods.sort ASC;
				");
		while($g = mysql_fetch_assoc($qLnk)){
			$data[$g['parent_level_alias']]['modified'] = $g['parent_level_modified'];
			$data[$g['parent_level_alias']]['ch'][$g['level_alias']]['modified'] = $g['level_modified'];
			$data[$g['parent_level_alias']]['ch'][$g['level_alias']]['ch'][$g['alias']]['modified'] = $g['modified'];
		}		
		
		return $data;
	}
	
	private function get_growers($data){
		$qLnk = mysql_query("
				SELECT
					modified,
					alias
				FROM
					growers
				WHERE
					alias <> ''
				ORDER BY
					name ASC;
				");
		while($g = mysql_fetch_assoc($qLnk)){
			$data['growers']['modified'] = '';
			$data['growers']['ch'][$g['alias']]['modified'] = $g['modified'];
		}
		
		$qLnk = mysql_query("SELECT MAX(modified) FROM growers;");
		$data['growers']['modified'] = mysql_result($qLnk,0);

		return $data;
	}
	
	private function get_articles($data){
		$qLnk = mysql_query("
				SELECT
					modified,
					alias
				FROM
					articles
				WHERE
					published = 1
					AND
					alias <> ''
				ORDER BY
					name ASC;
				");
		while($g = mysql_fetch_assoc($qLnk)){
			$data['articles']['modified'] = '';
			$data['articles']['ch'][$g['alias']]['modified'] = $g['modified'];
		}
		
		$qLnk = mysql_query("SELECT MAX(modified) FROM articles;");
		$data['articles']['modified'] = mysql_result($qLnk,0);

		return $data;
	}
	
	private function get_news($data){
		$qLnk = mysql_query("
				SELECT
					date AS modified,
					alias
				FROM
					news
				WHERE
					published = 1
					AND
					alias <> ''				
				ORDER BY
					name ASC;
				");
		while($g = mysql_fetch_assoc($qLnk)){
			$data['news']['modified'] = '';
			$data['news']['ch'][$g['alias']]['modified'] = $g['modified'];
		}
		
		$qLnk = mysql_query("SELECT MAX(date) FROM news;");
		$data['news']['modified'] = mysql_result($qLnk,0);
		
		return $data;
	}
	
	private function get_pages($data){
		$qLnk = mysql_query("
				SELECT
					modified,
					alias
				FROM
					pages
				WHERE
					published = 1
					AND
					in_sitemap = 1
					AND
					alias <> ''				
				ORDER BY
					name ASC;
				");
		while($g = mysql_fetch_assoc($qLnk)){
			$data[$g['alias']]['modified'] = $g['modified'];
		}
		
		return $data;
	}
	
	private function main_page(){
		$qLnk = mysql_query("
					SELECT 
						MAX(date) 
					FROM 
						news 
					WHERE 
						published = 1 
						AND 
						DATE(date) <= DATE(NOW());
				");
		return mysql_result($qLnk,0);		
	}
	
	public function get_data(){
		$data = $this->get_goods();
		$data = $this->get_growers($data);
		$data = $this->get_articles($data);
		$data = $this->get_news($data);
		$data = $this->get_pages($data);
		
		return array(
				'pages' => $data,
				'main_page' => $this->main_page()
				);
	}	
}
?>