<?php

class Adm_News_List extends Common_Rq
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    private function get_data()
    {
        $news = array();
        $qLnk = mysql_query( sprintf( "
				SELECT
					id, name, type, date, published
				FROM
					news
				%s
				ORDER BY
					date DESC;
				",
            (isset( $_GET[ 'type' ] ) && $_GET[ 'type' ])
                ? sprintf( "WHERE type = '%d'", $_GET[ 'type' ] )
                : ""
        ) );
        while ($n = mysql_fetch_assoc( $qLnk )) $news[] = $n;
        
        return $news;
    }
    
    private function get_classes($n)
    {
        $classes = array();
        
        if (!$n[ 'published' ]) $classes[] = 'tr_unpub';
        if ($n[ 'type' ] == 2) $classes[] = 'tr_type2';
        
        return implode( ' ', $classes );
    }
    
    public function print_list()
    {
        $news = $this->get_data();
        
        $html = array();
        
        $year_etal = false;
        foreach ($news as $n) {
            $type = Adm_News_Types::get_types( $n[ 'type' ] );
            
            $n[ 'year' ] = date( 'Y', strtotime( $n[ 'date' ] ) );
            $n[ 'print_year' ] = ($year_etal != $n[ 'year' ]);
            
            $n[ 'classes' ] = $this->get_classes( $n );
            
            $n[ 'type_name' ] = $type[ 0 ];
            $n[ 'type_color' ] = $type[ 1 ];
            
            $html[] = $this->do_rq( 'item', $n, true );
            
            $year_etal = $n[ 'year' ];
        }
        
        return implode( '', $html );
    }
    
    private function add_links()
    {
        $types = Adm_News_Types::get_types();
        
        $html = array();
        foreach ($types as $id => $arr) {
            
            $a = array(
                'id' => $id,
                'name' => $arr[ 0 ],
                'color' => $arr[ 1 ],
            );
            
            $html[] = $this->do_rq( 'add', $a, true );
        }
        
        return $this->do_rq( 'add',
            implode( '', $html )
        );
    }
    
    private function types_options()
    {
        $types = Adm_News_Types::get_types();
        
        $data = array();
        
        foreach ($types as $id => $arr)
            $data[] = array(
                'val' => $id,
                'name' => sprintf( 'новости %s',
                    mb_strtolower( $arr[ 0 ], 'utf-8' )
                ),
                'selected' => (isset( $_GET[ 'type' ] ) && $_GET[ 'type' ] == $id),
            );
        
        return Common_Template_Select::opts( $data, 'все новости' );
    }
    
    public function set_vars()
    {
        $vars = array(
            'list' => $this->print_list(),
            'add' => $this->add_links(),
            'options' => $this->types_options(),
        );
        
        foreach ($vars as $k => $v) $this->registry[ 'CL_template_vars' ]->set( $k, $v );
    }
    
}

