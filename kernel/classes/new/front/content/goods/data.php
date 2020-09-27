<?php

class Front_Content_Goods_Data
{

    private $registry;

    function __construct($registry)
    {
        $this->registry = $registry;
    }

    private function get_image($goods)
    {
        $qLnk = mysql_query( sprintf( "
        			SELECT
        				alias
        			FROM
        				goods_photo
        			WHERE
        				goods_id = '%d'
        			ORDER BY
        				sort ASC
        			LIMIT 1;
        			",
            $goods[ 'id' ]
        ) );
        $image = mysql_fetch_assoc( $qLnk );

        return Front_Catalog_Helper_Image::goods_path( $goods[ 'id' ], $image[ 'alias' ], '122x122' );
    }

    public function get_goods($barcode)
    {
        $qLnk = mysql_query( sprintf( "
					SELECT
						goods.id,
						goods.name,
						goods.alias,
						levels.alias AS level_alias,
						parent_tbl.alias AS parent_alias,
						growers.name AS grower_name
					FROM
						goods_barcodes
					INNER JOIN goods ON goods.id = goods_barcodes.goods_id
					INNER JOIN levels ON goods.level_id = levels.id
					INNER JOIN levels AS parent_tbl ON levels.parent_id = parent_tbl.id
					LEFT OUTER JOIN growers ON growers.id = goods.grower_id 
					WHERE
						goods_barcodes.barcode = '%s'
					",
            mysql_real_escape_string( $barcode )
        ) );
        $goods = mysql_fetch_assoc( $qLnk );
        if (!$goods) return false;

        $goods[ 'name' ] = ($goods[ 'grower_name' ])
            ? sprintf( '"%s". %s', $goods[ 'grower_name' ], $goods[ 'name' ] )
            : $goods[ 'name' ];

        $goods[ 'link' ] = sprintf( '/%s/%s/%s/',
            $goods[ 'parent_alias' ],
            $goods[ 'level_alias' ],
            $goods[ 'alias' ]
        );

        $goods[ 'image' ] = $this->get_image( $goods );

        return $goods;
    }
}

