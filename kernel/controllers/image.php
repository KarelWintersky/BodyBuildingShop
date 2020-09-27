<?php

class Controller_Image extends Controller_Base
{
    
    function index($path = NULL)
    {
        $this->image( $path );
    }
    
    function image($path = NULL)
    {
        if (is_null( $path ) || !count( $path )) {
            header( 'Location: /' );
            exit();
        }
        
        $Common_Image = new Common_Image( $this->registry );
        $Common_Image->resolve_path( $path );
    }
    
}



