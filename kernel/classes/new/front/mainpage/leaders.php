<?php
Class Front_Mainpage_Leaders Extends Common_Rq{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	private function get_data(){
		$goods = array();

		$qLnk = mysql_query("
				SELECT
					goods.id,
					goods.name,
					goods.alias,
					levels.alias AS level_alias,
					parent_tbl.alias AS parent_level_alias,
					goods_photo.alias AS avatar,
					goods_photo.goods_id AS photo_goods_id,
					growers.name AS grower_name				
				FROM
					goods_barcodes
				INNER JOIN goods ON goods.id = goods_barcodes.goods_id
				LEFT OUTER JOIN growers ON growers.id = goods.grower_id
				LEFT OUTER JOIN goods_photo ON goods_photo.id = goods.avatar_id
				INNER JOIN levels ON levels.id = goods.level_id
				INNER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id				 
				WHERE
					goods.published = 1
					AND
					goods.parent_barcode = 0				
					AND
					goods_barcodes.present = 1
					AND
					goods_barcodes.weight > 0
					AND
					goods_barcodes.price > 100		
				ORDER BY
					goods.popularity_index DESC
				LIMIT 3;						
				");
		while($g = mysql_fetch_assoc($qLnk)) $goods[] = $g;
			
		return $goods;
	}
	
	public function do_leaders(){
		$goods = $this->get_data();
		
		$html = array();
		foreach($goods as $g){
			$g['link'] = sprintf('/%s/%s/%s/',
					$g['parent_level_alias'],
					$g['level_alias'],
					$g['alias']
					);
			
			$g['avatar'] = Front_Catalog_Helper_Image::goods_path($g['photo_goods_id'],$g['avatar'],'80x80');
			
			$g['name'] = ($g['grower_name'])
				? sprintf('"%s". %s',$g['grower_name'],$g['name'])
				: $g['name'];			
			
			$html[] = $this->do_rq('item',$g,true);
		}
		
		return $this->do_rq('leaders',
				implode('',$html)
				);
	}		
}
?>