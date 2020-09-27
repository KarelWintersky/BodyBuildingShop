<?php

class Front_Order_Cart_Go
{
    
    private $registry;
    
    private $Front_Order_Storage;
    private $Front_Order_Cart_Coupon;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Order_Storage = new Front_Order_Storage( $this->registry );
        $this->Front_Order_Cart_Coupon = new Front_Order_Cart_Coupon( $this->registry );
    }
    
    private function write_gift()
    {
        $this->Front_Order_Storage->write_to_storage( 'gift',
            (isset( $_POST[ 'gift' ] )) ? $_POST[ 'gift' ] : false
        );
    }
    
    public function cart_go()
    {
        Front_Order_Post::do_check( 1 );
        
        $this->write_gift();
        
        $this->Front_Order_Cart_Coupon->apply_coupon( $_POST[ 'coupon' ], true );
        
        Front_Order_Steps::write_submit( 1 );
        
        header( 'Location: /order/login/check/' );
        exit();
    }
    
}

