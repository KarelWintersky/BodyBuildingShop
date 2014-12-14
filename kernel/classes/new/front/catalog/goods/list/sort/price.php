<?php
Class Front_Catalog_Goods_List_Sort_Price{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;		
	}
	
	private function type_cond($type){
		if($type=='level') return sprintf("AND goods.level_id = '%d'",$this->registry['level']['id']);
		
		if($type=='grower') return sprintf("AND goods.grower_id = '%d'",$this->registry['grower']['id']);
		
		return "";
	}
	
	public function get_sort($dir,$type){
		$ids = array(0);
		$qLnk = mysql_query(sprintf("
				SELECT
					goods_barcodes.goods_id
				FROM
					goods_barcodes
				INNER JOIN goods ON goods.id = goods_barcodes.goods_id
				WHERE
					goods_barcodes.goods_id <> '0'
					%s
				GROUP BY
					goods_barcodes.goods_id
				ORDER BY
					MIN(goods_barcodes.price) %s
				",
				$this->type_cond($type),
				$dir
				));
		while($g = mysql_fetch_assoc($qLnk)) $ids[] = $g['goods_id'];
		
		return sprintf("FIELD(goods.id,%s)",
				implode(",",$ids)
				);
	}
	
}
?>