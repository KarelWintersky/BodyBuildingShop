<?php
Class Front_Order_Mail_Goods{
	
	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}	

	private function get_rest($goods){
		$barcodes = array();
		foreach($goods as $g) $barcodes[$g['goods_barcode']] = $g['goods_barcode'];
			
		$qLnk = mysql_query(sprintf("
				SELECT
					goods_barcodes.barcode,
					goods_barcodes.feature,
					goods_barcodes.packing,
					goods.alias,
					levels.alias AS level_alias,
					parent_tbl.alias AS parent_alias,
					parent_tbl.id AS parent_parent_id
				FROM
					goods_barcodes
				INNER JOIN goods ON goods.id = goods_barcodes.goods_id
				INNER JOIN levels ON levels.id = goods.level_id
				INNER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
				WHERE
					goods_barcodes.barcode IN (%s);
				",
				implode(',',$barcodes)
		));	
		while($b = mysql_fetch_assoc($qLnk)){
			foreach($goods as $key => $g){
				if($g['goods_barcode']==$b['barcode'])
					$goods[$key] = $g + $b;
			}
		}	
		
		return $goods;
	}
	
	private function get_features($goods){
		foreach($goods as $key => $g){
			if(!$g['goods_feats_str'] || !is_numeric($g['goods_feats_str'])) continue;
				
			$qLnk = mysql_query(sprintf("
					SELECT 
						IFNULL(name,'') 
					FROM 
						features 
					WHERE 
						id = '%d'",
					$g['goods_feats_str']
					));
			$goods[$key]['goods_feats_str'] = mysql_result($qLnk,0);
		}		
		
		return $goods;
	}
	
	public function get_goods($num){
		$goods = array();
		$qLnk = mysql_query(sprintf("
				SELECT
					*				
				FROM
					orders_goods				
				WHERE
					order_id = '%s'
				ORDER BY
					final_price DESC;
				",
				implode('/',$num)
				));
		while($g = mysql_fetch_assoc($qLnk)) $goods[] = $g;
		
		$goods = $this->get_rest($goods);
		$goods = $this->get_features($goods);
		
		return $goods;
	}
			
}
?>