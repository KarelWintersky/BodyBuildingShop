<?php

class Front_Cart_Write
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    public function write_from_array($cart)
    {
        $string = array();
        
        foreach ($cart as $key => $arr) {
            $string[ $key ] = array(
                $arr[ 'barcode' ],
                $arr[ 'packing' ],
                $arr[ 'amount' ],
            );
            
            if ($arr[ 'color' ]) $string[ $key ][] = $arr[ 'color' ];
            
            $string[ $key ] = implode( ':', $string[ $key ] );
        }
        
        $string = implode( '|', $string );
        
        setcookie( 'thecart', $string, time() + 3600 * 24 * 300, '/' );
    }
}

