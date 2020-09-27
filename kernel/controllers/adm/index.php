<?php

class Controller_Index extends Controller_Base
{
    
    public function index($A = NULL)
    {
        $this->adm( $A );
    }
    
    public function adm($A = NULL)
    {
        $this->registry[ 'template' ]->set( 'tpl', 'adm' );
        $this->registry[ 'aias_path' ] = $A;
        
        if (count( $A ) == 0) {
            $this->registry[ 'f_404' ] = false;
            $this->registry[ 'template' ]->set( 'c', 'index_page' );
        } elseif (count( $A ) > 0 && isset( $this->registry[ 'userdata' ] ) && $this->registry[ 'userdata' ][ 'type' ] != 0 && $this->registry[ 'template' ]->main_part_check( $A[ 0 ] )) {
            $c = new $A[ 0 ]( $this->registry );
            $this->registry->set( $A[ 0 ], $c );
        }
        
    }
    
}



