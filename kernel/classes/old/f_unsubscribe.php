<?php

class f_Unsubscribe
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->registry->set( 'f_unsubscribe', $this );
        
        $this->do_unsubscribe();
    }
    
    public function pgc()
    {
    }
    
    public function path_check()
    {
        
        $path_arr = $this->registry[ 'route_path' ];
        
        $this->registry[ 'template' ]->add2crumbs( 'unsubscribe', 'Отписаться' );
        $this->registry[ 'noindex' ] = true;
        
        if (count( $path_arr ) == 0) {
            $this->registry[ 'f_404' ] = false;
            $this->registry[ 'template' ]->set( 'c', 'unsubscribe/main' );
            $this->registry[ 'longtitle' ] = 'Отписаться';
            return true;
        }
        
        return false;
    }
    
    private function do_unsubscribe()
    {
        if (isset( $_GET[ 't' ] ) && isset( $_GET[ 'u' ] ) && is_numeric( $_GET[ 'u' ] )) {
            if ($_GET[ 't' ] == 1) {
                $field = "get_news";
            } elseif ($_GET[ 't' ] == 2) {
                $field = "get_catalog_changes";
            } else {
                $field = false;
            }
            
            if ($field) {
                mysql_query( "UPDATE users SET users.".$field." = '0' WHERE users.id = '".$_GET[ 'u' ]."';" );
            }
            
        }
    }
    
    
}
