<?php

class Front_Order_Storage
{
    
    /*
     * класс для хранения всех данных в процессе заказа
     * все данные, кроме корзины, сохраняются в сессию
     * корзина сохраняется в куки, как и обычно
     * */
    
    private $registry;
    private $session_array_key = 'thecart';
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->registry->set( 'CL_storage', $this );
    }
    
    public function write_to_storage($key, $val)
    {
        $storage = $this->get_storage();
        
        $storage[ $key ] = $val;
        
        $string = json_encode( $storage );
        
        $_SESSION[ $this->session_array_key ] = $string;
    }
    
    public function get_storage($key = false)
    {
        if (!isset( $_SESSION[ $this->session_array_key ] ))
            return ($key) ? false : array();
        
        $storage = $_SESSION[ $this->session_array_key ];
        $storage = json_decode( $storage );
        
        $storage = Common_Useful::objectToArray( $storage );
        
        if (!$key) return $storage;
        
        return (isset( $storage[ $key ] ))
            ? $storage[ $key ]
            : false;
    }
    
    public function truncate_storage()
    {
        if (isset( $_SESSION[ $this->session_array_key ] ))
            unset( $_SESSION[ $this->session_array_key ] );
    }
    
    
}

