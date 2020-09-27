<?php

class Front_Order_Mail_Goods
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    private function get_rest($goods)
    {
        $barcodes = array();
        foreach ($goods as $g) $barcodes[ $g[ 'goods_barcode' ] ] = $g[ 'goods_barcode' ];
        
        $qLnk = mysql_query( sprintf( "
				SELECT
					goods_barcodes.barcode,
					goods_barcodes.feature,
					goods_barcodes.packing,
					goods.alias,
					levels.alias AS level_alias,
					parent_tbl.alias AS parent_alias,
					parent_tbl.id AS parent_parent_id
				FROM
					goods_barcodes
				INNER JOIN goods ON goods.id = goods_barcodes.goods_id
				INNER JOIN levels ON levels.id = goods.level_id
				INNER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
				WHERE
					goods_barcodes.barcode IN (%s);
				",
            implode( ',', $barcodes )
        ) );
        while ($b = mysql_fetch_assoc( $qLnk )) {
            foreach ($goods as $key => $g) {
                if ($g[ 'goods_barcode' ] == $b[ 'barcode' ])
                    $goods[ $key ] = $g + $b;
            }
        }
        
        return $goods;
    }
    
    private function get_features($goods)
    {
        foreach ($goods as $key => $g) {
            if (!$g[ 'goods_feats_str' ] || !is_numeric( $g[ 'goods_feats_str' ] )) continue;
            
            $qLnk = mysql_query( sprintf( "
					SELECT 
						IFNULL(name,'') 
					FROM 
						features 
					WHERE 
						id = '%d'",
                $g[ 'goods_feats_str' ]
            ) );
            $goods[ $key ][ 'goods_feats_str' ] = mysql_result( $qLnk, 0 );
        }
        
        return $goods;
    }
    
    private function add_gift_to_goods($goods, $order)
    {
        $gift = $order[ 'gift' ];
        if (!$gift) return $goods;
        
        $goods[] = array(
            'order_id' => $order[ 'num' ],
            'goods_id' => 0,
            'goods_barcode' => (isset( $gift[ 'barcode' ] )) ? $gift[ 'barcode' ] : false,
            'goods_packing' => (isset( $gift[ 'packing' ] )) ? $gift[ 'packing' ] : false,
            'goods_full_name' => (isset( $gift[ 'grower_name' ] ) && $gift[ 'grower_name' ])
                ? sprintf( '"%s". %s', $gift[ 'grower_name' ], $gift[ 'name' ] )
                : $gift[ 'name' ],
            'goods_feats_str' => '',
            'amount' => '1',
            'price' => '0',
            'discount' => '0',
            'final_price' => '0',
            'barcode' => (isset( $gift[ 'barcode' ] )) ? $gift[ 'barcode' ] : false,
            'feature' => (isset( $gift[ 'feature' ] )) ? $gift[ 'feature' ] : false,
            'packing' => (isset( $gift[ 'packing' ] )) ? $gift[ 'packing' ] : false,
            'alias' => (isset( $gift[ 'alias' ] )) ? $gift[ 'alias' ] : false,
            'level_alias' => (isset( $gift[ 'level_alias' ] )) ? $gift[ 'level_alias' ] : false,
            'parent_alias' => (isset( $gift[ 'parent_alias' ] )) ? $gift[ 'parent_alias' ] : false,
            'parent_parent_id' => (isset( $gift[ 'parent_id' ] )) ? $gift[ 'parent_id' ] : false,
        );
        
        return $goods;
    }
    
    
    public function get_goods($order)
    {
        $goods = array();
        $qLnk = mysql_query( sprintf( "
				SELECT
					*				
				FROM
					orders_goods				
				WHERE
					order_id = '%s'
				ORDER BY
					final_price DESC;
				",
            $order[ 'num' ]
        ) );
        while ($g = mysql_fetch_assoc( $qLnk )) $goods[] = $g;
        
        $goods = $this->get_rest( $goods );
        $goods = $this->get_features( $goods );
        
        $goods = $this->add_gift_to_goods( $goods, $order );
        
        return $goods;
    }
    
}

