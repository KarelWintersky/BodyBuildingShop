<?php
Class Front_Catalog_Helper_Image{

	public static function goods_path($goods_id,$alias,$size){
		$path = sprintf('/data/foto/goods/%s/%d/%s',
				$size,
				$goods_id,
				$alias
				);
		
		return $path;
	}

}
?>