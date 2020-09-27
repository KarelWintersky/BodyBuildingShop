<?php

class Controller_Index extends Controller_Base
{
    
    public function index($path = NULL)
    {
        
        $this->registry[ 'template' ]->set( 'tpl', 'adm' );
        
        $Adm_Orders_Content = new Adm_Orders_Content( $this->registry );
        
        if (!count( $path )) {
            $this->registry[ 'f_404' ] = false;
            
            $Adm_Orders_List = new Adm_Orders_List( $this->registry );
            $Adm_Orders_List->do_page();
            
            $this->registry[ 'template' ]->set( 'c', 'orders/list' );
        } elseif (count( $path ) == 1 && $Adm_Orders_Content->order_check( $path[ 0 ] )) {
            $this->registry[ 'f_404' ] = false;
            
            $this->registry[ 'template' ]->set( 'c', 'orders/order_' );
        }
        
    }
    
}


