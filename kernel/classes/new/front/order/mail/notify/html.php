<?php

class Front_Order_Mail_Notify_Html extends Common_Rq
{
    
    private $registry;
    
    private $Front_Order_Mail_Notify_Table;
    private $Front_Order_Done_Message;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Order_Mail_Notify_Table = new Front_Order_Mail_Notify_Table( $this->registry );
        $this->Front_Order_Done_Message = new Front_Order_Done_Message( $this->registry );
    }
    
    private function apply_styles($html)
    {
        $styles = array(
            'params_block width_2' => 'padding:0;background:#f2f2f2;padding:19px 30px 8px 20px;list-style:none;border:1px solid #ccc;',
            'params_block_li' => 'padding:0 0 10px;',
            'pb_label' => 'display:inline;font-weight:bold;',
            'pb_text' => 'display:inline;',
            'common_hint' => 'background:#fbf1d3;border-radius:2px;color:#333;padding:9px 35px 8px 15px;border:1px solid #e38a25;margin:0 0 20px 0;',
            'ch_h3' => 'margin:0 0 10px;color:#e38a25;font-weight:normal;font-size:18px;',
            'head_sum' => 'font-size:12px;font-weight:normal;color:#999;',
        );
        
        foreach ($styles as $k => $v) {
            $find = sprintf( 'class="%s"', $k );
            $replace = sprintf( 'style="%s"', $v );
            
            $html = str_replace( $find, $replace, $html );
        }
        
        return $html;
    }
    
    public function just_message($order)
    {
        /*
         * получить только сообщение с последнего шага - для письма оплаты картами
         * */
        
        $html = $this->Front_Order_Done_Message->do_message( $order );
        
        $html = $this->apply_styles( $html );
        
        $html = $this->registry[ 'CL_tpl_links' ]->do_links( $html );
        
        return $html;
    }
    
    public function print_html($order, $to_admin)
    {
        
        $delivery = Front_Order_Data_Delivery::get_methods( $order[ 'delivery_type' ] );
        $payment = Front_Order_Data_Payment::get_methods( $order[ 'payment_method_id' ] );
        
        $a = array(
            'num' => $order[ 'num' ],
            'date' => date( 'd.m.Y H:i', strtotime( $order[ 'made_on' ] ) ),
            'user_name' => $order[ 'tech' ][ 'name' ],
            'user_email' => $order[ 'tech' ][ 'email' ],
            'table' => $this->Front_Order_Mail_Notify_Table->print_goods( $order ),
            'wishes' => $order[ 'wishes' ],
            'delivery_name' => $delivery[ 'name' ],
            'payment_name' => ($payment) ? $payment[ 'name' ] : false,
            'delivery_type' => $order[ 'delivery_type' ],
            'nalog_costs' => $order[ 'nalog_costs' ],
            'delivery_costs' => (!is_null( $order[ 'delivery_costs' ] )) ? $order[ 'delivery_costs' ] : false,
            'overall_sum' => $order[ 'overall_sum' ],
            'from_account' => $order[ 'from_account' ],
            'lower_text' => $this->Front_Order_Done_Message->do_message( $order ),
            'to_admin' => $to_admin,
            'head_sum' => ($to_admin)
                ? sprintf( '%s руб.', Common_Useful::price2read( $order[ 'overall_sum' ] - $order[ 'from_account' ] ) )
                : false,
        );
        
        $html = $this->do_rq( 'tpl', $a );
        
        $html = $this->apply_styles( $html );
        
        $html = $this->registry[ 'CL_tpl_links' ]->do_links( $html );
        
        return $html;
    }
    
}

