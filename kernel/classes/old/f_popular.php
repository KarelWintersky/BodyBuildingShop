<?php

class f_Popular
{
    
    private $registry;
    
    public function pgc()
    {
    }
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->registry->set( 'f_popular', $this );
    }
    
    public function path_check()
    {
        
        $this->registry[ 'f_404' ] = false;
        $path_arr = $this->registry[ 'route_path' ];
        
        if (count( $path_arr ) == 0) {
            $this->registry[ 'template' ]->add2crumbs( 'popular', 'Лидеры продаж' );
            $this->registry[ 'template' ]->set( 'c', 'popular/index' );
            $this->registry[ 'longtitle' ] = 'Лидеры продаж';
            
            $this->registry[ 'cookie_type' ] = 'popular';
            
            $this->registry[ 'popular' ] = array( 'id' => 0 );
            
            $Front_Catalog_Popular = new Front_Catalog_Popular( $this->registry );
            $Front_Catalog_Popular->do_vars();
            
            $this->registry[ 'CL_css' ]->set( array(
                'catalog',
            ) );
            
            return true;
        }
        
        $this->registry[ 'f_404' ] = true;
        return false;
    }
    
    public function goods_list(&$list_html, &$list_params, &$reqiure_file)
    {
        $f_catalog = new f_Catalog( $this->registry );
        
        $f_catalog->goods_list( $list_html, $list_params, $reqiure_file, 2 );
    }
    
}
