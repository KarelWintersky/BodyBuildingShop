<?php

class Front_Order_Payment extends Common_Rq
{
    
    private $registry;
    
    private $Front_Order_Crumbs;
    private $Front_Order_Payment_Methods;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Order_Crumbs = new Front_Order_Crumbs( $this->registry );
        $this->Front_Order_Payment_Methods = new Front_Order_Payment_Methods( $this->registry );
    }
    
    private function print_classes($data)
    {
        $classes = array();
        
        $classes[] = 'fop_item';
        if ($data[ 'disabled' ]) $classes[] = 'disabled';
        
        return implode( ' ', $classes );
    }
    
    private function print_items($data)
    {
        $methods = $this->Front_Order_Payment_Methods->get_actual_list( $data );
        
        $html = array();
        foreach ($methods as $method_id => $arr) {
            
            $a = array(
                'name' => $arr[ 'name' ],
                'id' => $method_id,
                'checked' => ($arr[ 'active' ]) ? 'checked' : '',
                'classes' => $this->print_classes( $arr ),
                'text' => $this->do_text( $data, $arr, $method_id ),
                'disabled' => ($arr[ 'disabled' ]) ? 'disabled' : '',
            );
            
            $html[] = $this->do_rq( 'item', $a, true );
        }
        
        return implode( '', $html );
    }
    
    private function do_text($data, $arr, $method_id)
    {
        if (!$arr[ 'class_alias' ]) return $this->do_rq( 'text', $method_id );
        
        $classname = __CLASS__.'_'.$arr[ 'class_alias' ];
        $CL = new $classname( $this->registry );
        
        return $CL->do_text( $data );
    }
    
    public function do_vars()
    {
        $data = $this->registry[ 'CL_data' ]->get_data();
        
        $this->registry->set( 'longtitle', 'Выбор способа оплаты заказа' );
        
        $vars = array(
            'crumbs' => $this->Front_Order_Crumbs->do_crumbs( 3 ),
            'items' => $this->print_items( $data ),
        );
        
        foreach ($vars as $k => $v) $this->registry[ 'CL_template_vars' ]->set( $k, $v );
    }
    
}

