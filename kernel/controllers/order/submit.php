<?php

class Controller_Submit extends Controller_Base
{
    
    function submit($path = NULL)
    {
        Front_Order_Steps::check_step( 4 );
        
        $Front_Order_Write = new Front_Order_Write( $this->registry );
        $Front_Order_Write->do_write();
    }
    
    function index($path = NULL)
    {
        $this->submit( $path );
    }
    
}


