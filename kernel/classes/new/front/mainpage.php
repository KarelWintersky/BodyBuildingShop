<?php

class Front_Mainpage
{
    
    private $registry;
    
    private $Front_Mainpage_Goods;
    private $Front_Mainpage_Articles;
    private $Front_Mainpage_Growers;
    private $Front_Mainpage_Leaders;
    private $Front_Mainpage_News;
    private $Front_Mainpage_Module;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Mainpage_Goods = new Front_Mainpage_Goods( $this->registry );
        $this->Front_Mainpage_Articles = new Front_Mainpage_Articles( $this->registry );
        $this->Front_Mainpage_Growers = new Front_Mainpage_Growers( $this->registry );
        $this->Front_Mainpage_Leaders = new Front_Mainpage_Leaders( $this->registry );
        $this->Front_Mainpage_News = new Front_Mainpage_News( $this->registry );
        $this->Front_Mainpage_Module = new Front_Mainpage_Module( $this->registry );
    }
    
    private function get_content()
    {
        $qLnk = mysql_query( "
				SELECT
					*
				FROM
					pages
				WHERE
					alias = 'mainpage'
				LIMIT 1;
				" );
        $content = mysql_fetch_assoc( $qLnk );
        
        $this->registry[ 'seo_kw' ] = $content[ 'seo_kw' ];
        $this->registry[ 'seo_dsc' ] = $content[ 'seo_dsc' ];
        
        $this->registry[ 'longtitle' ] = $content[ 'seo_title' ];
        $this->registry[ 'h1' ] = $content[ 'name' ];
        
        return $content;
    }
    
    public function set_vars()
    {
        $content = $this->get_content();
        
        $vars = array(
            'h3' => $content[ 'h2_title' ],
            'text' => $content[ 'content' ],
            'goods' => $this->Front_Mainpage_Goods->do_goods(),
            'articles' => $this->Front_Mainpage_Articles->do_articles(),
            'growers' => $this->Front_Mainpage_Growers->do_growers(),
            'leaders' => $this->Front_Mainpage_Leaders->do_leaders(),
            'site_news' => $this->Front_Mainpage_News->site_news(),
            'nutrition_news' => $this->Front_Mainpage_News->nutrition_news(),
            'module' => $this->Front_Mainpage_Module->do_module(),
        );
        
        foreach ($vars as $k => $v) $this->registry[ 'CL_template_vars' ]->set( $k, $v );
    }
    
}

