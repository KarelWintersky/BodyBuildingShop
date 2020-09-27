<?php

class Adm_Pagination extends Common_Rq
{
    
    private $registry;
    private $adj;
    private $link;
    private $total;
    private $step;
    
    function __construct($registry, $link)
    {
        $this->registry = $registry;
        $this->registry->set( 'CL_pagination', $this );
        
        $this->adj = 3;
        $this->link = $this->mk_link( $link );
    }
    
    public function set_params($total, $step)
    {
        $this->total = $total;
        $this->step = $step;
    }
    
    private function mk_link($link)
    {
        $arr = explode( '?', $link );
        
        if (!isset( $arr[ 1 ] )) return $link.'?';
        
        $params = explode( '&', $arr[ 1 ] );
        
        foreach ($params as $key => $val) {
            $pair = explode( '=', $val );
            
            if ($pair[ 0 ] == 'page') unset( $params[ $key ] );
        }
        
        return (count( $params ))
            ? $arr[ 0 ].'?'.implode( '&', $params ).'&'
            : $arr[ 0 ].'?';
    }
    
    private function clr_link($link)
    {
        $link = rtrim( $link, '?' );
        $link = rtrim( $link, '&' );
        
        return $link;
    }
    
    public function print_paging()
    {
        $html = '';
        
        $pages_amount = ceil( $this->total / $this->step );
        
        if ($pages_amount == 1) return false;
        
        $cur = (isset( $_GET[ 'page' ] )) ? $_GET[ 'page' ] : 1;
        
        $html = array();
        
        $prev = array();
        $prev[ 'show' ] = ($cur > 1) ? true : false;
        $prev[ 'link' ] = ($cur - 1 == 1) ? $this->link : $this->link.'page='.($cur - 1);
        $prev[ 'link' ] = $this->clr_link( $prev[ 'link' ] );
        $html[] = $this->do_rq( 'prev', $prev, true );
        
        for ($i = 1; $i <= $pages_amount; $i++) {
            $a = array();
            $a[ 'i' ] = $i;
            $a[ 'class' ] = ($i == $cur) ? 'active' : '';
            $a[ 'lnk' ] = ($i == 1) ? $this->link : $this->link.'page='.$i;
            $a[ 'lnk' ] = $this->clr_link( $a[ 'lnk' ] );
            
            if ($i == 1 || $i == $pages_amount || ($i >= $cur - $this->adj && $i <= $cur + $this->adj)) {
                $html[] = $this->do_rq( 'item', $a, true );
            }
            
            if (($i == 1 && $cur - $this->adj > 2) || ($i == $pages_amount - 1 && $cur + $this->adj < $pages_amount - 1)) {
                $html[] = $this->do_rq( 'dotts', NULL, true );
            }
            
        }
        
        $next = array();
        $next[ 'show' ] = ($cur < $pages_amount) ? true : false;
        $next[ 'link' ] = $this->link.'page='.($cur + 1);
        $html[] = $this->do_rq( 'next', $next, true );
        
        return $this->do_rq( 'storage',
            implode( '', $html )
        );
    }
    
}

