<?php

class Adm_Prices_Excel_Array
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    private function goods_name($goods, $barcode)
    {
        $name = array();
        $name[] = ($goods[ 'grower' ]) ? sprintf( '"%s". %s', $goods[ 'grower' ], $goods[ 'name' ] ) : $goods[ 'name' ];
        if ($barcode[ 'feature' ]) $name[] = $barcode[ 'feature' ];
        
        return implode( ', ', $name );
    }
    
    private function make_goods($goods, $barcodes)
    {
        $output = array();
        
        foreach ($goods as $goods_id => $g) {
            foreach ($barcodes[ $goods_id ] as $b) {
                $new_price = ($g[ 'personal_discount' ]) ? round( $b[ 'price' ] - $b[ 'price' ] * $g[ 'personal_discount' ] / 100 ) : $b[ 'price' ];
                $new_price = Common_Useful::price2read( $new_price );
                
                $output[ $g[ 'level_id' ] ][] = array(
                    'name' => $this->goods_name( $g, $b ),
                    'url' => sprintf( '%s%s/%s/%s/',
                        THIS_URL,
                        $g[ 'parent_alias' ],
                        $g[ 'level_alias' ],
                        $g[ 'alias' ]
                    ),
                    'discount' => ($g[ 'personal_discount' ]) ? sprintf( '%s %%', $g[ 'personal_discount' ] ) : '',
                    'packing' => $b[ 'packing' ],
                    'price' => Common_Useful::price2read( $b[ 'price' ] ),
                    'new_price' => $new_price,
                    'new' => ($g[ 'new' ] == 1) ? 'новый' : '',
                    'present' => ($b[ 'present' ] == 1) ? 'да' : 'нет',
                    'present_val' => $b[ 'present' ],
                );
            }
        }
        
        return $output;
    }
    
    private function make_parents($goods)
    {
        $parents = array();
        
        foreach ($goods as $g)
            $parents[ $g[ 'parent_id' ] ] = array(
                'url' => sprintf( '%s%s/', THIS_URL, $g[ 'parent_alias' ] ),
                'name' => $g[ 'parent_name' ],
            );
        
        return $parents;
    }
    
    private function make_levels($goods)
    {
        $levels = array();
        
        foreach ($goods as $g)
            $levels[ $g[ 'parent_id' ] ][ $g[ 'level_id' ] ] = array(
                'url' => sprintf( '%s%s/%s/', THIS_URL, $g[ 'parent_alias' ], $g[ 'level_alias' ] ),
                'name' => $g[ 'level_name' ],
            );
        
        return $levels;
    }
    
    public function make_array($goods, $barcodes)
    {
        $output = array(
            'parents' => $this->make_parents( $goods ),
            'levels' => $this->make_levels( $goods ),
            'goods' => $this->make_goods( $goods, $barcodes ),
        );
        
        return $output;
    }
}

