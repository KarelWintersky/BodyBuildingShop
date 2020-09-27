<?php

class Database
{
    
    public function __construct($registry)
    {
        $db = $registry[ 'config' ][ 'db' ];
        
        $lnk = mysql_connect( $db[ 'host' ], $db[ 'u' ], $db[ 'p' ] ) or die( mysql_error() );
        mysql_select_db( $db[ 'db' ], $lnk );
        mysql_query( "SET NAMES UTF8;" );
    }
    
}
