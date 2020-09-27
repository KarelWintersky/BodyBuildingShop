<?php

class Front_Delivery extends Common_Rq
{
    
    private $registry;
    
    private $Front_Profile_Zipcode;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Profile_Zipcode = new Front_Profile_Zipcode( $this->registry );
    }
    
    public function page_extra()
    {
        if (!isset( $_GET[ 'index' ] )) return false;
        
        return $this->Front_Profile_Zipcode->zip_code_data( $_GET[ 'index' ] );
    }
    
}

