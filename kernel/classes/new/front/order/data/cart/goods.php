<?php
Class Front_Order_Data_Cart_Goods{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	private function get_goods($cart){
		$barcodes = array();
		foreach($cart as $key => $val){
			$key = explode(':',$key);
			
			$barcodes[] = sprintf("'%s'",$key[0]);
		}
		
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
			$g = $this->price_discount($g);
			
			$goods[$g['barcode']] = $g;
		}

		return $goods;
	}
	
	private function price_discount($g){
		$g['price'] = round($g['price']); //на всякий случай округляем, чтобы точно не было копеек
		
		$g['old_price'] = $g['price'];
		
		if($g['personal_discount']) $g['price'] = $g['price'] - $g['price']*$g['personal_discount']/100;
		$g['price'] = round($g['price']);
		
		return $g;
	}
	
	private function make_array($goods,$cart){
		/*
		 * для того, чтобы можно было добавить несколько строк товаров с одним штрихкодом
		 * одежда разных цветов, например
		 * */
		
		$output = array();
		foreach($cart as $key => $val){
			$arr = explode(':',$key);
				
			$g = $goods[$arr[0]];
				
			$g['amount'] = $val['amount'];
			$g['color'] = $val['color'];
				
			$output[$key] = $g;
		}
		
		return $output;		
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
		
		$goods = $this->get_goods($cart);
			$goods = $this->make_array($goods,$cart);
		
		$goods = $this->get_colors($cart,$goods);
		
		return $goods;		
	}
		
}
?>