<?php

class Controller_Cart extends Controller_Base
{
    
    function cart($path = NULL)
    {
        header( 'Location: /order/' );
        exit();
    }
    
    function index($path = NULL)
    {
        $this->cart( $path );
    }
    
}


