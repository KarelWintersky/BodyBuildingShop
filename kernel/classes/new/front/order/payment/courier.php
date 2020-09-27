<?php

class Front_Order_Payment_Courier extends Common_Rq
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    public function do_text($data)
    {
        
        /*Клиент зарегистрирован и из Питера или НЕ зарегистрирован, но ввел питерский индекс.*/
        if ($data[ 'costs' ][ 'courier' ][ 'is_spb' ])
            $type = 1;
        
        /*Клиент зарегистрирован НЕ из Питера – возможность закрыта*/
        elseif (!$data[ 'costs' ][ 'courier' ][ 'is_spb' ] && $this->registry[ 'userdata' ])
            $type = 2;
        
        /*Клиент НЕ зарегистрирован и ввел не местный индекс – возможность закрыта*/
        elseif (!$data[ 'costs' ][ 'courier' ][ 'is_spb' ] && !$this->registry[ 'userdata' ])
            $type = 3;
        
        $type = (isset( $type )) ? $type : false;
        
        return $this->do_rq( 'text', $type );
    }
    
}

