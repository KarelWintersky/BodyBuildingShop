<?php
Class Adm_Prices_Excel_Data{

	private $registry;
	
	private $Adm_Prices_Excel_Array;
	
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Adm_Prices_Excel_Array = new Adm_Prices_Excel_Array($this->registry);
	}
	
	private function get_goods(){
		$goods = array();
		$qLnk = mysql_query("
					SELECT
						goods.id,
						goods.level_id,
						goods.name,
						goods.alias,
						goods.price_1 AS price,
						goods.new,
						goods.personal_discount,
						goods.present,
						levels.name AS level_name,
						levels.alias AS level_alias,
						parent_tbl.id AS parent_id,
						parent_tbl.name AS parent_name,
						parent_tbl.alias AS parent_alias,
						growers.name AS grower
					FROM
						goods
					INNER JOIN levels ON levels.id = goods.level_id
					INNER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
					LEFT OUTER JOIN growers ON growers.id = goods.grower_id
					WHERE
						goods.published = '1'
						AND
						goods.parent_barcode = '0'
					ORDER BY
						parent_tbl.sort ASC,
						levels.sort ASC,
						goods.sort ASC;
				");
		while($g = mysql_fetch_assoc($qLnk)) $goods[$g['id']] = $g;
		
		return $goods;
	}
	
	private function get_barcodes($goods){
		$barcodes = array();
		$qLnk = mysql_query(sprintf("
				SELECT
					*
				FROM
					goods_barcodes
				WHERE
					goods_id IN (%s)
				",
				implode(",",array_keys($goods))
				));
		while($b = mysql_fetch_assoc($qLnk)) $barcodes[$b['goods_id']][] = $b;
		
		return $barcodes;
	}
	
	private function exclude_goods_without_barcodes($goods,$barcodes){
		foreach($goods as $goods_id => $g)
			if(!isset($barcodes[$goods_id]))
				unset($goods[$goods_id]);
		
		return $goods;
	}
	
	public function get_data(){
		$goods = $this->get_goods();
		$barcodes = $this->get_barcodes($goods);
		
		$goods = $this->exclude_goods_without_barcodes($goods,$barcodes);
		
		return $this->Adm_Prices_Excel_Array->make_array($goods,$barcodes);
	}
	
}
?>