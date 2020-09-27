<?php

class Front_Profile
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    public function get_data()
    {
        $qLnk = mysql_query( sprintf( "
				SELECT 
					* 
				FROM 
					users 
				WHERE 
					id = '%d';",
            $_SESSION[ 'user_id' ]
        ) );
        $data = mysql_fetch_assoc( $qLnk );
        
        $this->do_vars( $data );
        
        return $data;
    }
    
    public function do_vars($data)
    {
        $vars = array(
            'name' => $data[ 'name' ],
            'id' => $data[ 'id' ],
            'discount' => $data[ 'personal_discount' ],
            'max_nalog_sum' => $data[ 'max_nalog' ],
            'my_account' => $data[ 'my_account' ],
        );
        
        foreach ($vars as $k => $v) $this->registry[ 'CL_template_vars' ]->set( $k, $v );
    }
    
}

