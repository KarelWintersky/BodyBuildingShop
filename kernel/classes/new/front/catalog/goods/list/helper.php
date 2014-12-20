<?php
Class Front_Catalog_Goods_List_Helper{

	public static function get_type($from){
		return ($from==0) ? 'level' : (($from==1) ? 'grower' : 'popular');
	}
			
}
?>