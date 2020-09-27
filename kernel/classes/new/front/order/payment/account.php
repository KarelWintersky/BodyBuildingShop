<?php

class Front_Order_Payment_Account extends Common_Rq
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    public function do_text($data)
    {
        
        /*Клиент зарегистрирован, деньги есть*/
        if ($this->registry[ 'userdata' ] && $this->registry[ 'userdata' ][ 'my_account' ])
            $type = 1;
        
        /*Клиент зарегистрирован, денег нет*/
        elseif ($this->registry[ 'userdata' ] && !$this->registry[ 'userdata' ][ 'my_account' ])
            $type = 2;
        
        /*Клиент НЕ зарегистрирован*/
        elseif (!$this->registry[ 'userdata' ])
            $type = 3;
        
        $type = (isset( $type )) ? $type : false;
        
        return $this->do_rq( 'text', $type );
    }
    
}

