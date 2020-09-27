<?php

class Front_Order_Write_Goods
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    private function do_query($q)
    {
        if (!count( $q )) return false;
        
        mysql_query( sprintf( "
				INSERT INTO
					orders_goods
						(
							order_id,
							goods_barcode,
							goods_full_name,
							goods_packing,
							amount,
							price,
							discount,
							final_price,
							goods_feats_str
						)
					VALUES
						%s
				",
            implode( ", ", $q )
        ) );
        
    }
    
    public function do_write($order_num, $data)
    {
        
        $q = array();
        foreach ($data[ 'cart' ] as $key => $arr) {
            $goods = $data[ 'goods' ][ $key ];
            
            $discount = $goods[ 'personal_discount' ];
            $name = ($goods[ 'grower_name' ])
                ? sprintf( '"%s". %s', $goods[ 'grower_name' ], $goods[ 'name' ] )
                : $goods[ 'name' ];
            
            $q[] = sprintf( "
						(
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s'
						)					
					",
                $order_num,
                $arr[ 'barcode' ],
                mysql_real_escape_string( $name ),
                mysql_real_escape_string( $arr[ 'packing' ] ),
                $arr[ 'amount' ],
                $goods[ 'old_price' ],
                $goods[ 'personal_discount' ],
                $goods[ 'price' ],
                mysql_real_escape_string( $arr[ 'color' ] )
            );
        }
        
        $this->do_query( $q );
    }
}

