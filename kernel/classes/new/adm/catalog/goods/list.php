<?php

class Adm_Catalog_Goods_List extends Common_Rq
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    private function get_goods($level_id)
    {
        $goods = array();
        $qLnk = mysql_query( sprintf( "
				SELECT DISTINCT
					goods.id,
					goods.name,
					goods.parent_barcode,
					goods.barcode,
					goods.published,
					goods.hot,
					goods.new,
					goods.price_1,
					goods.personal_discount,
					goods.packing,
					goods.alias,
					growers.name AS grower,
					IF(ISNULL(goods_barcodes.barcode),0,1) AS present
				FROM
					goods
				LEFT OUTER JOIN growers ON growers.id = goods.grower_id
				LEFT OUTER JOIN goods_barcodes 
					ON 
						goods_barcodes.goods_id = goods.id
						AND
						goods_barcodes.present = 1
				WHERE
					goods.level_id = '%d'
				ORDER BY
					goods.published DESC,
					goods.sort ASC;
				",
            $level_id
        ) );
        while ($g = mysql_fetch_assoc( $qLnk )) $goods[] = $g;
        
        return $goods;
    }
    
    public function print_list($level_id)
    {
        $goods = $this->get_goods( $level_id );
        
        $html = array();
        $i = 1;
        foreach ($goods as $g) {
            $g[ 'sort' ] = $i;
            
            $html[] = $this->do_rq( 'list', $g, true );
            $i++;
        }
        
        return implode( '', $html );
    }
}

