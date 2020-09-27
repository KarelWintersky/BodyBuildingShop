<?php

class Front_Order_Done extends Common_Rq
{
    
    private $registry;
    
    private $Front_Order_Done_Data;
    private $Front_Order_Done_Message;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Order_Done_Data = new Front_Order_Done_Data( $this->registry );
        $this->Front_Order_Done_Message = new Front_Order_Done_Message( $this->registry );
    }
    
    public function do_vars()
    {
        $order = $this->Front_Order_Done_Data->get_data();
        if (!$order) {
            header( 'Location: /order/' );
            exit();
        }
        
        $this->registry->set( 'longtitle', 'Ваш заказ успешно сформирован' );
        
        $vars = array(
            'num' => $order[ 'num' ],
            'message' => $this->Front_Order_Done_Message->do_message( $order ),
            'bill' => $this->do_bill( $order ),
            'social' => $this->do_rq( 'social', NULL ),
        );
        
        foreach ($vars as $k => $v) $this->registry[ 'CL_template_vars' ]->set( $k, $v );
    }
    
    private function do_bill($order)
    {
        if ($order[ 'payment_method_id' ] != 2) return false;
        
        return '<input type="hidden" id="openbill" value="1">';
    }
    
}

