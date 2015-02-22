<?php
Class Adm_Orders_Goods_Barcodes{

	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}
	
	public function get_data($goods){
		$barcodes = array();
		foreach($goods as $g)
			if($g['goods_barcode'])
				$barcodes[] = $g['goods_barcode'];
				
		$qLnk = mysql_query(sprintf("
				SELECT
					goods_barcodes.barcode,
					goods_barcodes.packing,
					goods_barcodes.feature,
					goods.id,
					goods.name AS goods_name,
					levels.id AS level_id,
					parent_tbl.id AS parent_id
				FROM
					goods_barcodes
				INNER JOIN goods ON goods.id = goods_barcodes.goods_id
				INNER JOIN levels ON levels.id = goods.level_id
				INNER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
				WHERE
					goods_barcodes.barcode IN (%s)
				",
				implode(",",$barcodes)
		));
		while($g = mysql_fetch_assoc($qLnk)){
			foreach($goods as $key => $gitem){
				if($gitem['goods_barcode']==$g['barcode']){
					$goods[$key]['goods_id'] = $g['id'];
					$goods[$key]['goods_name'] = $g['goods_name'];
					$goods[$key]['level_id'] = $g['level_id'];
					$goods[$key]['parent_id'] = $g['parent_id'];
					$goods[$key]['packing'] = $g['packing'];
					$goods[$key]['feature'] = $g['feature'];
				}
			}
		}

		return $goods;
	}
	
}
?>