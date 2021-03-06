<?php

class Front_Order_Payment_Write
{
    
    private $registry;
    
    private $Front_Order_Storage;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Order_Storage = new Front_Order_Storage( $this->registry );
    }
    
    public function do_write()
    {
        Front_Order_Post::do_check( 3 );
        $method_id = $_POST[ 'payment' ];
        
        $methods = Front_Order_Data_Payment::get_methods();
        if (!isset( $methods[ $method_id ] )) return false;
        
        $this->Front_Order_Storage->write_to_storage( 'payment', $method_id );
        
        Front_Order_Steps::write_submit( 3 );
    }
    
}

