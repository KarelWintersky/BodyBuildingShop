<?php

class Front_Mainpage_Growers extends Common_Rq
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    private function get_data()
    {
        $growers = array();
        $qLnk = mysql_query( "
				SELECT
					id,
					name,
					alias,
					avatar
				FROM
					growers
				WHERE
					goods_count > 0
					AND
					id IN (14,5,16,17,2,3,7,1)
				ORDER BY
					sort ASC;
				" );
        while ($g = mysql_fetch_assoc( $qLnk )) $growers[] = $g;
        
        return $growers;
    }
    
    public function do_growers()
    {
        $growers = $this->get_data();
        
        $html = array();
        foreach ($growers as $g)
            $html[] = $this->do_rq( 'item', $g, true );
        
        return $this->do_rq( 'growers',
            implode( '', $html )
        );
    }
}

