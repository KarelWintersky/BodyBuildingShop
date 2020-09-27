<?php

class Front_Order_Bill_Num
{
    
    /*
     * извлекаем номер счета из GET-переменной либо из сессии
     * */
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    public function get_num()
    {
        if (isset( $_GET[ 'o' ] ))
            return array(
                'num' => $_GET[ 'o' ],
                'skip_user_match' => false,
            );
        
        if (isset( $_SESSION[ 'done_order_num' ] ))
            return array(
                'num' => $_SESSION[ 'done_order_num' ],
                'skip_user_match' => true,
            );
        
        return false;
    }
}

