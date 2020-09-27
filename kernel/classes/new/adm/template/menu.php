<?php

class Adm_Template_Menu extends Common_Rq
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    public function print_menu($parent_id)
    {
        $html = array();
        $qLnk = mysql_query( sprintf( "
				SELECT
					main_parts.alias,
					main_parts.name,
					parent_tbl.alias AS parent_alias
				FROM
					main_parts
				INNER JOIN main_parts AS parent_tbl ON parent_tbl.id = main_parts.parent_id
				WHERE
					main_parts.parent_id = '%d'
				ORDER BY
					main_parts.sort ASC;
				",
            $parent_id
        ) );
        while ($r = mysql_fetch_assoc( $qLnk )) {
            $r[ 'active' ] = (
                (
                    count( $this->registry[ 'sub_aias_path' ] ) == 0 && !$r[ 'alias' ])
                ||
                (count( $this->registry[ 'sub_aias_path' ] ) > 0 && $r[ 'alias' ] == $this->registry[ 'sub_aias_path' ][ 0 ]))
                ? 'active'
                : '';
            
            $html[] = $this->do_rq( 'sidebar', $r, true );
        }
        
        return $this->do_rq( 'sidebar', implode( '', $html ) );
    }
}

