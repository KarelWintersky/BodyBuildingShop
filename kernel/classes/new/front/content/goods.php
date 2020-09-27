<?php

class Front_Content_Goods extends Common_Rq
{
    
    /*класс превращает строки с товарами в контенте в блок товара
     * строка типа {{g:003944204814|Текст сверху|Текст снизу|r}}
     * */
    
    private $registry;
    
    private $Front_Content_Goods_Data;
    
    function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Content_Goods_Data = new Front_Content_Goods_Data( $this->registry );
    }
    
    private function do_classes($arr)
    {
        $classes = array();
        
        $classes[] = 'goods_content_block';
        $classes[] = $arr[ 3 ];
        
        if (isset( $arr[ 4 ] ) && $arr[ 4 ]) $classes[] = 'text_center';
        
        return implode( ' ', $classes );
    }
    
    private function do_replace($arr)
    {
        $goods = $this->Front_Content_Goods_Data->get_goods( $arr[ 0 ] );
        if (!$goods) return false;
        
        $a = array(
            'upper_text' => ($arr[ 1 ]) ? $arr[ 1 ] : false,
            'lower_text' => ($arr[ 2 ]) ? $arr[ 2 ] : false,
            'classes' => $this->do_classes( $arr ),
            'goods' => $goods,
        );
        
        return $this->do_rq( 'storage', $a );
        
    }
    
    private function match_find($matches)
    {
        $arr = explode( '|', $matches[ 1 ] );
        if (count( $arr ) != 4 && count( $arr ) != 5) return $matches[ 0 ];
        
        return $this->do_replace( $arr );
    }
    
    public function string_to_goods($text)
    {
        $reg = "/{{g:(.*)}}/i";
        $text = preg_replace_callback( $reg, array( $this, 'match_find' ), $text );
        
        return $text;
    }
}

