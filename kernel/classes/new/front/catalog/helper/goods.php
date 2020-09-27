<?php

class Front_Catalog_Helper_Goods
{

    public static function list_name($goods)
    {
        $name = array();

        if ($goods[ 'grower_id' ]) $name[] = sprintf( '"%s".', $goods[ 'grower' ] );
        $name[] = ($goods[ 'seo_h1' ])
            ? $goods[ 'seo_h1' ]
            : $goods[ 'name' ];

        return implode( ' ', $name );
    }

}

