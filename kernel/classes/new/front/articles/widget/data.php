<?php

class Front_Articles_Widget_Data
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    public function get_data($ids)
    {
        $articles = array();
        
        $qLnk = mysql_query( sprintf( "
				SELECT
					name,
					alias,
					introtext,
					avatar
				FROM
					articles
				WHERE
					id IN (%s)
					AND
					published = 1
				ORDER BY
					FIELD(id, %s)
				",
            implode( ",", $ids ),
            implode( ",", $ids )
        ) );
        while ($a = mysql_fetch_assoc( $qLnk )) $articles[] = $a;
        
        return $articles;
    }
    
}

