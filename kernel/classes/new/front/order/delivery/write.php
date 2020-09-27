<?php

class Front_Order_Delivery_Write
{
    
    private $registry;
    
    private $Front_Order_Storage;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Order_Storage = new Front_Order_Storage( $this->registry );
    }
    
    private function write_courier()
    {
        foreach ($_POST[ 'courier' ] as $name => $value) {
            $key = sprintf( 'courier_%s', $name );
            
            $this->Front_Order_Storage->write_to_storage( $key, $value );
        }
    }
    
    private function write_self()
    {
        foreach ($_POST[ 'self' ] as $name => $value) {
            $key = sprintf( 'self_%s', $name );
            
            $this->Front_Order_Storage->write_to_storage( $key, $value );
        }
    }
    
    public function do_write()
    {
        Front_Order_Post::do_check( 2 );
        $method_id = $_POST[ 'delivery' ];
        
        $methods = Front_Order_Data_Delivery::get_methods();
        if (!isset( $methods[ $method_id ] )) return false;
        
        $this->write_courier();
        $this->write_self();
        
        $this->Front_Order_Storage->write_to_storage( 'delivery', $method_id );
        
        Front_Order_Steps::write_submit( 2 );
    }
    
}

