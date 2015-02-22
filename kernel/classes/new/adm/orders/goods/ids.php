<?php
Class Adm_Orders_Goods_Ids{

	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}
		
	public function get_data($goods){
		$ids = array();
		foreach($goods as $g) $ids[] = $g['goods_id'];
		
		$qLnk = mysql_query(sprintf("
				SELECT
					goods.id,
					levels.id AS level_id,
					parent_tbl.id AS parent_id
				FROM
					goods
				INNER JOIN levels ON levels.id = goods.level_id
				INNER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
				WHERE
					goods.id IN (%s)
				",
				implode(",",$ids)
		));
		while($g = mysql_fetch_assoc($qLnk)){
			foreach($goods as $key => $arr){
				if($arr['goods_id']==$g['id']){
					$goods[$key]['level_id'] = $g['level_id'];
					$goods[$key]['parent_id'] = $g['parent_id'];
				}
			}
		}	
		
		return $goods;
	}	
	
}
?>