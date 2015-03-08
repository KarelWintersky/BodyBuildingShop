<?php
Class Front_Mainpage_Goods Extends Common_Rq{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	private function get_data(){
		$goods = array();
		$qLnk = mysql_query(sprintf("
				SELECT
					goods.name,
					goods.alias,
					goods.present,
					goods.new,
					(goods.personal_discount + %s) AS discount,
					goods.price_1,
					levels.alias AS level_alias,
					parent_tbl.alias AS parent_level_alias,
					goods_photo.alias AS avatar,
					goods_photo.alt AS alt,
					growers.name AS grower_name,				
					goods_barcodes.goods_id,
					MIN(goods_barcodes.price) AS price,
					COUNT(goods_barcodes.goods_id) AS the_count			
				FROM
					goods_barcodes
				INNER JOIN goods ON goods.id = goods_barcodes.goods_id
				INNER JOIN levels ON levels.id = goods.level_id
				INNER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id	
				LEFT OUTER JOIN goods_photo ON goods_photo.id = goods.avatar_id
				LEFT OUTER JOIN growers ON growers.id = goods.grower_id
				WHERE
					goods.published = 1
					AND
					goods.hot = 1
					AND
					goods_barcodes.present = 1
					AND
					goods_barcodes.weight > 0				
				GROUP BY
					goods_barcodes.goods_id
				ORDER BY RAND()
				LIMIT 6
				",
				OVERALL_DISCOUNT
				));
		while($g = mysql_fetch_assoc($qLnk)) $goods[] = $g;
				
		return $goods;
	}
	
	public function do_goods(){
		$goods = $this->get_data();
		
		$html = array(); $i = 1;
		foreach($goods as $g){
			$g['num'] = $i;

			$g['link'] = sprintf('/%s/%s/%s/',
					$g['parent_level_alias'],
					$g['level_alias'],
					$g['alias']
					);
			$g['name'] = ($g['grower_name'])
				? sprintf('"%s". %s',$g['grower_name'],$g['name'])
				: $g['name'];
			
			$g['avatar'] = ($g['avatar'])
				? Front_Catalog_Helper_Image::goods_path($g['goods_id'],$g['avatar'],'122x122')
				: false;
			
			$html[] = $this->do_rq('item',$g,true);
		
			$i++;
		}		
		
		return $this->do_rq('goods',
				implode('',$html)
				);
	}
		
}
?>