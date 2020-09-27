<?php

class Front_Order_Login_Check
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    public function if_authed()
    {
        /*
         * проверяем, авторизован ли покупатель
         * если да - редиректим на следующий шаг - выбор доставки
         * если нет - редиректим на страницу /order/login/
         * */
        
        if ($this->registry[ 'userdata' ]) {
            header( 'Location: /order/delivery/' );
        } else {
            header( 'Location: /order/login/' );
        }
        
        exit();
    }
    
}

