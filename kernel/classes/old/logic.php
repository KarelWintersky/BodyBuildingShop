<?php

class Logic
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    public function register_params()
    {
        $qLnk = mysql_query( "SELECT params.name, params.value FROM params" );
        while ($p = mysql_fetch_assoc( $qLnk )) {
            define( $p[ 'name' ], $p[ 'value' ], true );
        }
    }
    
}
