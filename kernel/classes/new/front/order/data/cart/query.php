<?php
Class Front_Order_Data_Cart_Query{
		
	public static function do_query($barcodes){
		$qLnk = mysql_query(sprintf("
				SELECT
					goods.id AS goods_id,
					goods.name,
					goods.alias,
					goods.grower_id,
					goods.personal_discount + %s AS personal_discount,
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
				OVERALL_DISCOUNT,
				implode(",",$barcodes)
				));

		return $qLnk;
	}
			
}
?>