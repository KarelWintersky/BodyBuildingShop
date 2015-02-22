<?php
Class Adm_Orders_Goods{

	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}
		
	public function goods_list(){
		$goods = array();
		$barcodes = array();
		$qLnk = mysql_query("
				SELECT
				orders_goods.*
				FROM
				orders_goods
				WHERE
				orders_goods.order_id = '".$this->registry['order_info']['num']."'
				ORDER BY
				orders_goods.final_price DESC;
				");
		while($g = mysql_fetch_assoc($qLnk)){
			$goods[] = $g;
			$barcodes[] = $g['goods_barcode'];
		}
		if(count($goods)==0) return '';
			
		$is_barcodes = false;
		foreach($goods as $g) if($g['goods_id']==0) $is_barcodes = true;
			
		if($is_barcodes){
				
			foreach($barcodes as $key => $val) if(!$val) unset($barcodes[$key]);
				
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
					LEFT OUTER JOIN levels ON levels.id = goods.level_id
					LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
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
	
		}else{
			$ids = array();
			foreach($goods as $g) $ids[] = $g['goods_id'];
				
			$qLnk = mysql_query(sprintf("
					SELECT
					goods.id,
					levels.id AS level_id,
					parent_tbl.id AS parent_id
					FROM
					goods
					LEFT OUTER JOIN levels ON levels.id = goods.level_id
					LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
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
		}
	
		foreach($goods as $g){
			$g['goods_full_name'] = ($g['goods_full_name']=='' && isset($g['goods_name']))
			? $g['goods_name']
			: $g['goods_full_name'];
				
			$g['final_price'] = $g['final_price']*$g['amount'];
				
			$this->item_rq('goods_item',$g);
		}
	}
	
}
?>