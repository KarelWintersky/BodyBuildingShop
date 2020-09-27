<?php

class Front_Template_Links
{
    
    private $registry;
    private $domains;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->registry->set( 'CL_tpl_links', $this );
        
        $this->domains = array(
            'http://new2.bodybuilding-shop.ru',
            'http://www.bodybuilding-shop.ru',
            'http://bodybuilding-shop.ru',
            'http://bodybuilding-shop',
        );
    }
    
    private function to_lowercase($link)
    {
        /*
         * проверяем, если ссылка на файл, а не на страницу, в lowercase не переводим
         * если в последней части урл есть точка, то файл
         * */
        $link_trimmed = trim( $link, '/' );
        $arr = explode( '/', $link_trimmed );
        $end = array_pop( $arr );
        if (strpos( $end, '.' ) !== false) return $link;
        
        $link = mb_strtolower( $link, 'utf-8' );
        
        return $link;
    }
    
    private function replace_domain($link)
    {
        $is_outer = false;
        if (strpos( $link, 'http://' ) !== false || strpos( $link, 'https://' ) !== false) {
            $is_outer = true;
            foreach ($this->domains as $d) {
                if (strpos( $d, $link ) !== false) {
                    $is_outer = false;
                }
            }
        }
        if ($is_outer) return $link;
        
        foreach ($this->domains as $d)
            $link = str_replace( $d, '', $link );
        
        return sprintf( '%s/%s',
            rtrim( THIS_URL, '/' ),
            ltrim( $link, '/' )
        );
    }
    
    private function replace_index_slash($matches)
    {
        return str_replace(
            'href="/"',
            sprintf( 'href="%s"', THIS_URL ),
            $matches[ 0 ]
        );
    }
    
    private function do_replace($matches)
    {
        if (strpos( $matches[ 1 ], '#' ) === 0) return $matches[ 0 ];
        if (strpos( $matches[ 1 ], 'mailto' ) === 0) return $matches[ 0 ];
        if (!$matches[ 1 ]) return $matches[ 0 ];
        if ($matches[ 1 ] == '/') return $this->replace_index_slash( $matches );
        
        $link = $matches[ 1 ];
        $link = $this->to_lowercase( $link );
        $link = $this->replace_domain( $link );
        
        
        return str_replace(
            $matches[ 1 ],
            $link,
            $matches[ 0 ]
        );
    }
    
    public function do_links($html)
    {
        $reg = '/<a href=\"([^\"]*)\".*>.*<\/a>/iU';
        $html = preg_replace_callback(
            $reg,
            array( $this, 'do_replace' ),
            $html
        );
        
        return $html;
    }
    
}

