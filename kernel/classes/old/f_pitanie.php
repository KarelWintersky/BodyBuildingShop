<?php

class f_Pitanie
{
    
    private $registry;
    
    private $Front_Articles_Widget;
    private $Front_Content_Goods;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->registry->set( 'f_articles', $this );
        
        $this->Front_Articles_Widget = new Front_Articles_Widget( $this->registry );
        $this->Front_Content_Goods = new Front_Content_Goods( $this->registry );
    }
    
    public function path_check($alias)
    {
        
        $this->registry[ 'f_404' ] = false;
        
        if ($this->article_exists( $alias )) {
            $this->registry[ 'template' ]->set( 'c', 'articles/article' );
            return true;
        }
        
        $this->registry[ 'f_404' ] = true;
        return false;
    }
    
    public function item_rq($name, $a = NULL)
    {
        require($this->registry[ 'template' ]->TF.'item/articles/'.$name.'.html');
    }
    
    private function article_exists($alias)
    {
        $qLnk = mysql_query( "
								SELECT
									articles.*
								FROM
									articles
								WHERE
									articles.alias = '".$alias."'
									AND
									articles.published = 1
								LIMIT 1;
								" );
        if (mysql_num_rows( $qLnk ) > 0) {
            $article = mysql_fetch_assoc( $qLnk );
            $article[ 'content' ] = $this->Front_Articles_Widget->do_articles( $article[ 'content' ] );
            $article[ 'content' ] = $this->Front_Content_Goods->string_to_goods( $article[ 'content' ] );
            
            $this->registry[ 'article' ] = $article;
            
            $this->registry[ 'longtitle' ] = $this->registry[ 'article' ][ 'longtitle' ];
            $this->registry[ 'seo_kw' ] = $this->registry[ 'article' ][ 'seo_kw' ];
            $this->registry[ 'seo_dsc' ] = $this->registry[ 'article' ][ 'seo_dsc' ];
            
            $this->registry[ 'template' ]->add2crumbs( 'pitanie', 'Обмен опытом' );
            $this->registry[ 'template' ]->add2crumbs( $this->registry[ 'article' ][ 'alias' ], $this->registry[ 'article' ][ 'name' ] );
            
            return true;
        }
        
        return false;
    }
    
    
}
