<?php

class f_Pages
{
    
    private $registry;
    
    private $Front_Articles_Widget;
    private $Front_Content_Goods;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        $f_articles = new f_Pitanie( $this->registry );
        
        $this->Front_Articles_Widget = new Front_Articles_Widget( $this->registry );
        $this->Front_Content_Goods = new Front_Content_Goods( $this->registry );
    }
    
    public function path_check()
    {
        
        $this->registry[ 'f_404' ] = false;
        $path_arr = $this->registry[ 'route_path' ];
        
        if (count( $path_arr ) == 1 && $this->page_exists( $path_arr[ 0 ] )) {
            $this->registry[ 'template' ]->set( 'c', 'pages/page' );
            return true;
        } elseif (count( $path_arr ) == 1 && $this->registry[ 'f_articles' ]->path_check( $path_arr[ 0 ] )) {
            return true;
        }
        
        $this->registry[ 'f_404' ] = true;
        return false;
    }
    
    public function item_rq($name, $a = NULL)
    {
        require($this->registry[ 'template' ]->TF.'item/pages/'.$name.'.html');
    }
    
    private function page_exists($alias)
    {
        $qLnk = mysql_query( "
								SELECT
									pages.*
								FROM
									pages
								WHERE
									pages.alias = '".$alias."'
									AND
									pages.alias <> 'mainpage'
								LIMIT 1;
								" );
        if (mysql_num_rows( $qLnk ) > 0) {
            $page = mysql_fetch_assoc( $qLnk );
            $page[ 'content' ] = $this->Front_Articles_Widget->do_articles( $page[ 'content' ] );
            $page[ 'content' ] = $this->Front_Content_Goods->string_to_goods( $page[ 'content' ] );
            
            $this->registry[ 'page' ] = $page;
            
            $this->registry[ 'longtitle' ] = $this->registry[ 'page' ][ 'seo_title' ];
            $this->registry[ 'seo_kw' ] = $this->registry[ 'page' ][ 'seo_kw' ];
            $this->registry[ 'seo_dsc' ] = $this->registry[ 'page' ][ 'seo_dsc' ];
            
            $this->registry[ 'template' ]->add2crumbs( $this->registry[ 'page' ][ 'alias' ], $this->registry[ 'page' ][ 'name' ] );
            
            return true;
        }
        
        return false;
    }
    
}
