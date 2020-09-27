<?php

class Adm_News_Save_Site
{
    
    /*
     * дополнительные действия по сохранению новостей сайта
     * */
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    public function check_alias($alias, $id)
    {
        $qLnk = mysql_query( sprintf( "
				SELECT
					COUNT(*)
				FROM
					news
				WHERE
					alias = '%s'
					AND
					type = '1'
					AND
					id <> '%d';
				",
            mysql_real_escape_string( $alias ),
            $id
        ) );
        
        return (mysql_result( $qLnk, 0 ) == 0);
    }
    
}

