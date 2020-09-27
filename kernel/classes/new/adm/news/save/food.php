<?php

class Adm_News_Save_Food
{
    
    /*
     * дополнительные действия по сохранению новостей спортивного питания
     * */
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    private function in_news($alias, $id)
    {
        $qLnk = mysql_query( sprintf( "
				SELECT
					COUNT(*)
				FROM
					news
				WHERE
					alias = '%s'
					AND
					type = '2'
					AND
					id <> '%d';
				",
            mysql_real_escape_string( $alias ),
            $id
        ) );
        
        return (mysql_result( $qLnk, 0 ) == 0);
    }
    
    private function in_pages($alias)
    {
        $qLnk = mysql_query( sprintf( "
				SELECT
					COUNT(*)
				FROM
					pages
				WHERE
					alias = '%s'
				",
            mysql_real_escape_string( $alias )
        ) );
        
        return (mysql_result( $qLnk, 0 ) == 0);
    }
    
    private function in_articles($alias)
    {
        $qLnk = mysql_query( sprintf( "
				SELECT
					COUNT(*)
				FROM
					articles
				WHERE
					alias = '%s'
				",
            mysql_real_escape_string( $alias )
        ) );
        
        return (mysql_result( $qLnk, 0 ) == 0);
    }
    
    private function in_catalog($alias)
    {
        $qLnk = mysql_query( sprintf( "
				SELECT
					COUNT(*)
				FROM
					levels
				WHERE
					alias = '%s'
					AND
					parent_id = '0'
				",
            mysql_real_escape_string( $alias )
        ) );
        
        return (mysql_result( $qLnk, 0 ) == 0);
    }
    
    public function check_alias($alias, $id)
    {
        if (!$this->in_news( $alias, $id )
            ||
            !$this->in_pages( $alias )
            ||
            !$this->in_articles( $alias )
            ||
            !$this->in_catalog( $alias )
        ) return false;
        
        return true;
    }
    
}

