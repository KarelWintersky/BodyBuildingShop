<?php

class Front_Mainpage_News extends Common_Rq
{
    
    private $registry;
    
    private $Front_Avatar;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Avatar = new Front_Avatar( $this->registry, 'news' );
    }
    
    private function get_data($type, $limit)
    {
        $news = array();
        $qLnk = mysql_query( sprintf( "
				SELECT
					id,
					name,
					date,
					introtext,
					alias
				FROM
					news
				WHERE
					published = 1
					AND
					type = '%d'
				ORDER BY
					date DESC
				LIMIT %d;
				",
            $type, $limit
        ) );
        
        while ($n = mysql_fetch_assoc( $qLnk )) $news[ $n[ 'id' ] ] = $n;
        
        return $news;
    }
    
    public function nutrition_news()
    {
        $news = $this->get_data( 2, 2 );
        $news = $this->Front_Avatar->list_avatars( $news, 1, 1 );
        
        $html = array();
        foreach ($news as $n)
            $html[] = $this->do_rq( 'nutrition', $n, true );
        
        $type = Front_News_Data::get_type( 2, 'id' );
        
        $a = array(
            'link' => sprintf( '/%s/', $type[ 'alias' ] ),
            'list' => implode( '', $html ),
        );
        
        return $this->do_rq( 'nutrition', $a );
    }
    
    public function site_news()
    {
        $news = $this->get_data( 1, 3 );
        
        $html = array();
        foreach ($news as $n)
            $html[] = $this->do_rq( 'site', $n, true );
        
        return $this->do_rq( 'site',
            implode( '', $html )
        );
    }
}

