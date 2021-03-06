<?php

class Front_Order_Write_Input
{
    
    /*
     * "выходные" данные - то, что в корзине и вообще человек указывал в процессе заказа
     * */
    
    private $registry;
    
    private $Front_Order_Storage;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Order_Storage = new Front_Order_Storage( $this->registry );
    }
    
    private function get_phone($deilvery)
    {
        if ($deilvery == 1) return false;
        
        if ($deilvery == 2) return $this->Front_Order_Storage->get_storage( 'courier_phone' );
        
        if ($deilvery == 4) return $this->Front_Order_Storage->get_storage( 'self_phone' );
        
        return false;
    }
    
    private function courier_data($deilvery)
    {
        if ($deilvery != 2) return false;
        
        $arr = array(
            $this->Front_Order_Storage->get_storage( 'courier_name' ),
            $this->Front_Order_Storage->get_storage( 'courier_phone' ),
            $this->Front_Order_Storage->get_storage( 'courier_zipcode' ),
            $this->Front_Order_Storage->get_storage( 'courier_city' ),
            $this->Front_Order_Storage->get_storage( 'courier_street' ),
            $this->Front_Order_Storage->get_storage( 'courier_house' ),
            $this->Front_Order_Storage->get_storage( 'courier_email' ),
        );
        
        foreach ($arr as $key => $val) $arr[ $key ] = str_replace( '::', '', $val );
        
        return implode( '::', $arr );
    }
    
    private function self_data($deilvery)
    {
        if ($deilvery != 4) return false;
        
        $arr = array(
            $this->Front_Order_Storage->get_storage( 'self_name' ),
            $this->Front_Order_Storage->get_storage( 'self_phone' ),
        );
        
        foreach ($arr as $key => $val) $arr[ $key ] = str_replace( '::', '', $val );
        
        return implode( '::', $arr );
    }
    
    private function is_spb($data, $deilvery)
    {
        /*
         * для облегчения дальнейших действий записываем, из Санкт-Петербурга ли покупатель
         * */
        
        if ($deilvery == 1) return $data[ 'costs' ][ 'post' ][ 'is_spb' ];
        
        if ($deilvery == 2) return $data[ 'costs' ][ 'courier' ][ 'is_spb' ];
        
        if ($deilvery == 4 && $this->registry[ 'userdata' ])
            return (isset( $data[ 'costs' ][ 'post' ][ 'is_spb' ] ))
                ? $data[ 'costs' ][ 'post' ][ 'is_spb' ]
                : false;
        
        return true;
    }
    
    public function make_data($data)
    {
        $deilvery = $this->Front_Order_Storage->get_storage( 'delivery' );
        $payment = $this->Front_Order_Storage->get_storage( 'payment' );
        
        $nalog_costs = ($deilvery == 1 && $payment == 1) ? $data[ 'nalog' ] : 0;
        
        $input = array(
            'account_extra_payment' => (isset( $_POST[ 'extrapayment' ] )) ? $_POST[ 'extrapayment' ] : false,
            'wishes' => (isset( $_POST[ 'wishes' ] )) ? $_POST[ 'wishes' ] : false,
            'payment_method' => $payment,
            'delivery_type' => $deilvery,
            'coupon' => $this->Front_Order_Storage->get_storage( 'coupon' ),
            'phone' => $this->get_phone( $deilvery ),
            'coupon_discount' => $this->Front_Order_Storage->get_storage( 'coupon_discount' ),
            'courier_data' => $this->courier_data( $deilvery ),
            'self_data' => $this->self_data( $deilvery ),
            
            'sum' => $data[ 'sum' ],
            'sum_with_discount' => $data[ 'sum_with_discount' ],
            'delivery_costs' => $data[ 'delivery_sum' ],
            'nalog_costs' => $nalog_costs,
            'overall_sum' => $data[ 'sum_with_discount' ] + $data[ 'delivery_sum' ] + $nalog_costs,
            'gift_barcode' => ($data[ 'gift' ])
                ? ((isset( $data[ 'gift' ][ 'barcode' ] )) ? $data[ 'gift' ][ 'barcode' ] : 0)
                : false,
            'is_spb' => $this->is_spb( $data, $deilvery ),
        );
        
        return $input;
    }
}

