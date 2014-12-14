<?php
Class Front_Catalog_Goods_List_Sort_Present{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;		
	}
	
	private function type_cond($type){
		if($type=='level') return sprintf("AND goods.level_id = '%d'",$this->registry['level']['id']);
	
		if($type=='grower') return sprintf("AND goods.grower_id = '%d'",$this->registry['grower']['id']);
	
		return "";
	}	
	
	private function do_query($present,$type){
		$ids = array();
		$qLnk = mysql_query(sprintf("
				SELECT
					goods_barcodes.goods_id
				FROM
					goods_barcodes
				INNER JOIN goods ON goods.id = goods_barcodes.goods_id
				WHERE
					goods_barcodes.goods_id <> '0'
					AND
					goods_barcodes.present = '%d'
					%s
				GROUP BY
					goods_barcodes.goods_id
				ORDER BY
					goods.name ASC;
				",
				$present,
				$this->type_cond($type)
		));
		while($g = mysql_fetch_assoc($qLnk)) $ids[] = $g['goods_id'];

		return $ids;
	}
	
	public function get_sort($dir,$type){
		$ids = array_merge(
				array(0),
				$this->do_query(1,$type),
				$this->do_query(0,$type)
				);
		
		return sprintf("FIELD(goods.id,%s)",
				implode(",",$ids)
				);
	}
	
}
?>