<?php

class Front_Catalog_Goods_List_Display extends Common_Rq
{
    
    private $registry;
    private $values;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->registry->set( 'CL_catalog_display', $this );
        
        $this->values = array(
            'list' => 'Подробно',
            'table' => 'Списком',
        );
        
    }
    
    private function display_from_cookie($type)
    {
        $display = (isset( $_COOKIE[ $this->registry[ 'cookie_type' ] ][ 'display_type' ][ $this->registry[ $type ][ 'id' ] ] ))
            ? $_COOKIE[ $this->registry[ 'cookie_type' ] ][ 'display_type' ][ $this->registry[ $type ][ 'id' ] ]
            : 'list';
        
        return $display;
    }
    
    private function display_type_crutch($display_type)
    {
        /*
         * костыль для приведения старых названий к новым
        * */
        
        if ($display_type == 'goods_list_table') return 'table';
        if ($display_type == 'goods_list') return 'list';
        
        return $display_type;
    }
    
    public function get_display_type($from)
    {
        $type = Front_Catalog_Goods_List_Helper::get_type( $from );
        
        $display = $this->display_from_cookie( $type );
        
        $display = $this->display_type_crutch( $display );
        
        return $display;
    }
    
    public function print_display_types($from)
    {
        $html = array();
        
        $cur = $this->get_display_type( $from );
        foreach ($this->values as $id => $name) {
            
            $a = array(
                'id' => $id,
                'class' => ($cur == $id) ? 'active' : '',
                'onclick' => ($cur != $id) ? 'display_type_change(this);' : '',
                'name' => $name,
            );
            
            $html[] = $this->do_rq( 'type', $a, true );
        }
        
        return implode( '', $html );
    }
    
}

