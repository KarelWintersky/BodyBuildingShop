<?php

class Common_Address
{
    
    public static function from_courier($courier_data)
    {
        /*
         * собираем адрес из данных о покупателе для курьерской доставки, где он хранится в другом формате
         * */
        
        $courier_data = explode( '::', $courier_data );
        
        $address = array(
            'zip_code' => (isset( $courier_data[ 2 ] )) ? $courier_data[ 2 ] : '',
            'city' => (isset( $courier_data[ 3 ] )) ? $courier_data[ 3 ] : '',
            'street' => (isset( $courier_data[ 4 ] )) ? $courier_data[ 4 ] : '',
            'house' => (isset( $courier_data[ 5 ] )) ? $courier_data[ 5 ] : '',
            'corpus' => false,
            'flat' => false,
        );
        
        return self::implode_address( $address );
    }
    
    public static function implode_address($address)
    {
        $string = array(
            $address[ 'zip_code' ],
            'Россия',
            $address[ 'city' ],
            $address[ 'street' ],
            ($address[ 'house' ]) ? sprintf( 'д. %s', $address[ 'house' ] ) : false,
            ($address[ 'corpus' ]) ? sprintf( 'корп. %s', $address[ 'corpus' ] ) : false,
            ($address[ 'flat' ]) ? sprintf( 'кв. %s', $address[ 'flat' ] ) : false,
        );
        
        foreach ($string as $k => $v) if (!$v) unset( $string[ $k ] );
        
        return (count( $string ) > 1) ? implode( ', ', $string ) : false;
    }
    
}

