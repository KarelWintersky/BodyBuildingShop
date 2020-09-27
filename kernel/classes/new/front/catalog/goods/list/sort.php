<?php

class Front_Catalog_Goods_List_Sort
{
    
    private $registry;
    private $values;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->registry->set( 'CL_catalog_sort', $this );
        
        $this->values = array(
            1 => array( 'name' => 'умолчанию', 'q' => 'sort ASC' ),
            2 => array( 'name' => 'наличию', 'q' => 'present::' ),
            3 => array( 'name' => 'алфавиту А - Я', 'q' => 'seo_h1 ASC' ),
            4 => array( 'name' => 'алфавиту Я - А', 'q' => 'seo_h1 DESC' ),
            5 => array( 'name' => 'цене, сначала дешевые', 'q' => 'price::ASC' ),
            6 => array( 'name' => 'цене, сначала дорогие', 'q' => 'price::DESC' ),
            7 => array( 'name' => 'производителю', 'q' => 'grower ASC' ),
            8 => array( 'name' => 'популярности', 'q' => 'popularity_index DESC' ),
        );
    }
    
    private function sort_from_cookie($type)
    {
        return (isset( $_COOKIE[ $this->registry[ 'cookie_type' ] ][ 'sort' ][ $this->registry[ $type ][ 'id' ] ] ))
            ? $_COOKIE[ $this->registry[ 'cookie_type' ] ][ 'sort' ][ $this->registry[ $type ][ 'id' ] ]
            : false;
    }
    
    public function print_options($from)
    {
        $type = Front_Catalog_Goods_List_Helper::get_type( $from );
        
        $sort = $this->sort_from_cookie( $type );
        
        $data = array();
        foreach ($this->values as $key => $arr)
            $data[] = array(
                'val' => $key,
                'name' => $arr[ 'name' ],
                'selected' => ($sort == $key),
            );
        
        return Common_Template_Select::opts( $data );
    }
    
    private function make_sort_query($string, $type)
    {
        $arr = explode( '::', $string );
        if (count( $arr ) == 1) return $string;
        
        $class = sprintf( '%s_%s', __CLASS__, $arr[ 0 ] );
        
        $CL = new $class( $this->registry );
        return $CL->get_sort( $arr[ 1 ], $type );
    }
    
    public function get_sort($from)
    {
        $type = Front_Catalog_Goods_List_Helper::get_type( $from );
        
        $sort = $this->sort_from_cookie( $type );
        
        if ($sort && isset( $this->values[ $sort ] ))
            return $this->make_sort_query(
                $this->values[ $sort ][ 'q' ],
                $type
            );
        
        return ($from == 2)
            ? "goods.popularity_index DESC"
            : "goods.sort ASC";
    }
    
    
}

