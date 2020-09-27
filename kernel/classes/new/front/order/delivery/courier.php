<?php

class Front_Order_Delivery_Courier extends Common_Rq
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    public function do_text($data)
    {
        $arr = $data[ 'costs' ][ 'courier' ];
        
        /*человек зарегистрирован и не из Питера*/
        if ($this->registry[ 'userdata' ] && !$arr[ 'is_spb' ])
            $type = 1;
        
        /*человек зарегистрирован, из Питера и набрал на бесплатную доставку*/
        elseif ($this->registry[ 'userdata' ] && $arr[ 'is_spb' ] && $data[ 'sum_with_discount' ] >= FREE_DELIVERY_SUM)
            $type = 2;
        
        /*человек зарегистрирован, из Питера и НЕ набрал на бесплатную доставку*/
        elseif ($this->registry[ 'userdata' ] && $arr[ 'is_spb' ] && $data[ 'sum_with_discount' ] < FREE_DELIVERY_SUM)
            $type = 3;
        
        /*человек НЕ зарегистрирован и не из Питера*/
        elseif (!$this->registry[ 'userdata' ] && !$arr[ 'is_spb' ])
            $type = 4;
        
        /*человек НЕ зарегистрирован и из Питера и набрал на бесплатную доставку*/
        elseif (!$this->registry[ 'userdata' ] && $arr[ 'is_spb' ] && $data[ 'sum_with_discount' ] >= FREE_DELIVERY_SUM)
            $type = 5;
        
        /*человек НЕ зарегистрирован и из Питера и НЕ набрал на бесплатную доставку*/
        elseif (!$this->registry[ 'userdata' ] && $arr[ 'is_spb' ] && $data[ 'sum_with_discount' ] < FREE_DELIVERY_SUM)
            $type = 6;
        
        $a = array(
            'type' => (isset( $type )) ? $type : false,
            'order_sum' => Common_Useful::price2read( $data[ 'sum_with_discount' ] ),
            'delivery_sum' => COURIER_SPB_COST,
            'diff' => Common_Useful::price2read( FREE_DELIVERY_SUM - $data[ 'sum_with_discount' ] ),
        );
        
        return $this->do_rq( 'text', $a );
    }
    
    public function extra_fields()
    {
        
        $a = array(
            'name' => ($this->registry[ 'CL_storage' ]->get_storage( 'courier_name' ))
                ? $this->registry[ 'CL_storage' ]->get_storage( 'courier_name' )
                : (($this->registry[ 'userdata' ]) ? $this->registry[ 'userdata' ][ 'name' ] : ''),
            'phone' => ($this->registry[ 'CL_storage' ]->get_storage( 'courier_phone' ))
                ? $this->registry[ 'CL_storage' ]->get_storage( 'courier_phone' )
                : (($this->registry[ 'userdata' ]) ? $this->registry[ 'userdata' ][ 'phone' ] : ''),
            'email' => ($this->registry[ 'CL_storage' ]->get_storage( 'courier_email' ))
                ? $this->registry[ 'CL_storage' ]->get_storage( 'courier_email' )
                : (($this->registry[ 'userdata' ]) ? $this->registry[ 'userdata' ][ 'email' ] : ''),
            'zipcode' => ($this->registry[ 'CL_storage' ]->get_storage( 'courier_zipcode' ))
                ? $this->registry[ 'CL_storage' ]->get_storage( 'courier_zipcode' )
                : (($this->registry[ 'userdata' ]) ? $this->registry[ 'userdata' ][ 'zip_code' ] : ''),
            'city' => ($this->registry[ 'CL_storage' ]->get_storage( 'courier_city' ))
                ? $this->registry[ 'CL_storage' ]->get_storage( 'courier_city' )
                : (($this->registry[ 'userdata' ]) ? $this->registry[ 'userdata' ][ 'city' ] : ''),
            'street' => ($this->registry[ 'CL_storage' ]->get_storage( 'courier_street' ))
                ? $this->registry[ 'CL_storage' ]->get_storage( 'courier_street' )
                : (($this->registry[ 'userdata' ]) ? $this->registry[ 'userdata' ][ 'street' ] : ''),
            'house' => ($this->registry[ 'CL_storage' ]->get_storage( 'courier_house' ))
                ? $this->registry[ 'CL_storage' ]->get_storage( 'courier_house' )
                : (($this->registry[ 'userdata' ]) ? $this->registry[ 'userdata' ][ 'house' ] : ''),
        );
        
        return $this->do_rq( 'fields', $a );
    }
    
    public function calculate_cost($data)
    {
        $arr = $data[ 'costs' ][ 'courier' ];
        
        $a = array(
            'price' => Common_Useful::price2read( $arr[ 'sum' ] ),
            'is_spb' => $arr[ 'is_spb' ],
        );
        
        return $this->do_rq( 'cost', $a );
    }
    
    public function recalculate()
    {
        $Front_Order_Storage = new Front_Order_Storage( $this->registry );
        $Front_Order_Data = new Front_Order_Data( $this->registry );
        
        $Front_Order_Storage->write_to_storage( 'courier_zipcode', $_POST[ 'zipcode' ] );
        $Front_Order_Storage->write_to_storage( 'courier_city', $_POST[ 'city' ] );
        
        $data = $Front_Order_Data->get_data();
        
        $output = array(
            'cost' => $this->calculate_cost( $data ),
            'text' => $this->do_text( $data ),
        );
        
        echo json_encode( $output );
    }
}

