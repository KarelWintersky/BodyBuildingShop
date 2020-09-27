<?php

class Front_News
{
    
    private $registry;
    
    private $Front_News_Page;
    private $Front_News_List;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_News_Page = new Front_News_Page( $this->registry );
        $this->Front_News_List = new Front_News_List( $this->registry );
    }
    
    public function path_check()
    {
        /*
         * проверка url новостей того или иного вида
         * */
        
        $path = $this->registry[ 'route_path' ];
        
        //старница новостей сайта
        if (count( $path ) == 1 && $this->Front_News_List->list_check( $path[ 0 ] )) return true;
        
        //новость спортивного питания
        elseif (count( $path ) == 1 && $this->Front_News_Page->news_check( $path[ 0 ], 2 )) return true;
        
        //новость сайта
        elseif (count( $path ) == 2 && $path[ 0 ] == 'news' && $this->Front_News_Page->news_check( $path[ 1 ], 1 )) return true;
        
        else return false;
    }
    
}

