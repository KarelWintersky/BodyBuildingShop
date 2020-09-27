<?php

class Front_Order_Check_Courier extends Common_Rq
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    public function notify_block($data)
    {
        $delivery = $this->registry[ 'CL_storage' ]->get_storage( 'delivery' );
        if ($delivery != 2) return false;
        
        if (!isset( $data[ 'costs' ][ 'courier' ][ 'is_spb' ] ) || $data[ 'costs' ][ 'courier' ][ 'is_spb' ]) return false;
        
        return $this->do_rq( 'notify', NULL );
    }
    
}

