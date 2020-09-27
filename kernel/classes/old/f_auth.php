<?php

class f_Auth
{
    
    private $registry;
    
    public function pgc()
    {
    }
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->registry->set( 'f_auth', $this );
    }
    
    public function path_check()
    {
        
        if (isset( $_SESSION[ 'user_id' ] )) {
            header( 'Location: /' );
        }
        
        $this->registry[ 'f_404' ] = false;
        $path_arr = $this->registry[ 'route_path' ];
        
        $this->registry[ 'template' ]->add2crumbs( 'auth', 'Авторизация' );
        $this->registry[ 'noindex' ] = true;
        
        if (count( $path_arr ) == 0) {
            $this->registry[ 'template' ]->set( 'c', 'auth/main' );
            $this->registry[ 'longtitle' ] = 'Авторизация';
            
            $this->registry[ 'CL_css' ]->set( array(
                'profile',
            ) );
            
            return true;
        }
        
        $this->registry[ 'f_404' ] = true;
        return false;
    }
    
}
