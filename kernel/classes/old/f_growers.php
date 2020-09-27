<?php

class f_Growers
{
    
    private $registry;
    
    public function pgc()
    {
    }
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->registry->set( 'f_growers', $this );
    }
    
    public function path_check()
    {
        
        $this->registry[ 'f_404' ] = false;
        $path_arr = $this->registry[ 'route_path' ];
        
        $this->registry[ 'template' ]->add2crumbs( 'growers', 'Партнеры' );
        
        if (count( $path_arr ) == 0) {
            $this->registry[ 'template' ]->set( 'c', 'growers/list' );
            $this->registry[ 'longtitle' ] = 'Каталог партнеров';
            return true;
        } elseif (count( $path_arr ) == 1 && $this->grower_exists( $path_arr[ 0 ] )) {
            $this->registry[ 'template' ]->set( 'c', 'growers/grower' );
            
            $Front_Growers = new Front_Growers( $this->registry );
            $Front_Growers->do_vars();
            
            $this->registry[ 'CL_css' ]->set( array(
                'catalog',
            ) );
            
            return true;
        }
        
        $this->registry[ 'f_404' ] = true;
        return false;
    }
    
    public function item_rq($name, $a = NULL)
    {
        require($this->registry[ 'template' ]->TF.'item/growers/'.$name.'.html');
    }
    
    private function grower_exists($alias)
    {
        $qLnk = mysql_query( "
								SELECT
									growers.*
								FROM
									growers
								WHERE
									growers.alias = '".$alias."'
									AND
									growers.goods_count > 0
								LIMIT 1;
								" );
        if (mysql_num_rows( $qLnk ) > 0) {
            $grower = mysql_fetch_assoc( $qLnk );
            $grower[ 'level_link' ] = '/growers/'.$grower[ 'alias' ].'/';
            $this->registry[ 'grower' ] = $grower;
            
            $longtitle = isset( $_GET[ 'page' ] ) ? $this->registry[ 'grower' ][ 'longtitle' ].' - страница '.$_GET[ 'page' ] : $this->registry[ 'grower' ][ 'longtitle' ];
            $this->registry[ 'longtitle' ] = $longtitle;
            $this->registry[ 'seo_kw' ] = $this->registry[ 'grower' ][ 'seo_kw' ];
            $this->registry[ 'seo_dsc' ] = $this->registry[ 'grower' ][ 'seo_dsc' ];
            
            $this->registry[ 'template' ]->add2crumbs( $this->registry[ 'grower' ][ 'alias' ], $this->registry[ 'grower' ][ 'name' ] );
            
            $this->registry[ 'cookie_type' ] = 'grower';
            
            return true;
        }
        return false;
    }
    
    public function goods_list(&$list_html, &$list_params, &$reqiure_file)
    {
        $f_catalog = new f_Catalog( $this->registry );
        
        $f_catalog->goods_list( $list_html, $list_params, $reqiure_file, 1 );
    }
    
    public function growers_catalog()
    {
        $qLnk = mysql_query( "
								SELECT
									growers.id,
									growers.name,
									growers.alias,
									growers.avatar
								FROM
									growers
								WHERE
									growers.goods_count > 0
								ORDER BY
									growers.name ASC;
								" );
        $i = 1;
        while ($g = mysql_fetch_assoc( $qLnk )) {
            $g[ 'class' ] = ($i % 4 == 0) ? 'r' : '';
            $this->item_rq( 'list', $g );
            
            $i++;
        }
    }
    
}
