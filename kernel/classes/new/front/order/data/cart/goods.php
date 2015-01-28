<?php
Class Front_Order_Data_Cart_Goods{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	private function get_goods($cart){
		$barcodes = array();
		foreach($cart as $key => $val) $barcodes[] = sprintf("'%s'",$key);
		
		$goods = array();
		
		$qLnk = mysql_query(sprintf("
				SELECT
					goods.id AS goods_id,
					goods.name,
					goods.alias,
					goods.grower_id,
					goods.personal_discount,
					goods.delivery_way_id,
					levels.alias AS level_alias,
					parent_tbl.alias AS parent_alias,
					parent_tbl.id AS root_id,
					growers.name AS grower_name,
					goods_barcodes.barcode,
					goods_barcodes.packing,
					goods_barcodes.price,
					goods_barcodes.feature,
					goods_barcodes.weight
				FROM
					goods_barcodes
				INNER JOIN goods ON goods.id = goods_barcodes.goods_id
				INNER JOIN levels ON levels.id = goods.level_id
				INNER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
				LEFT OUTER JOIN growers ON growers.id = goods.grower_id				
				WHERE
					goods_barcodes.barcode IN (%s)
					AND
					goods.published = 1
				",
				implode(",",$barcodes)
				));
		while($g = mysql_fetch_assoc($qLnk)){
			$g['amount'] = $cart[$g['barcode']]['amount'];
			$g['color'] = $cart[$g['barcode']]['color'];
			
			$goods[$g['barcode']] = $g;
		}
				
		return $goods;
	}
	
	private function get_colors($cart,$goods){
		/*
		 * выбор цветов в массив. для раздела "Одежда"
		 * */
		$keys = array();
		foreach($cart as $barcode => $line){
			if(!$line['color'] || !isset($goods[$barcode])) continue;
			
			$qLnk = mysql_query(sprintf("
					SELECT
						IFNULL(name,'')
					FROM
						features
					WHERE
						id = '%d'
					",$line['color']));
			
			$goods[$barcode]['color_name'] = mysql_result($qLnk,0);
		}
		
		return $goods;	
	}
	
	public function get_data($cart){
		if(!$cart) return false;
		
		$goods = $this->get_colors($cart,
				$this->get_goods($cart)
				);
		
		return $goods;		
	}
		
}
?>