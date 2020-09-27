<?php

class Front_Order_Data_Delivery_Courier
{
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    private function is_spb($zipcode_data)
    {
        $courier_city = $this->registry[ 'CL_storage' ]->get_storage( 'courier_city' );
        if ($courier_city) {
            $courier_city = trim( $courier_city );
            $courier_city = mb_strtolower( $courier_city, 'utf-8' );
            
            if (strpos( $courier_city, 'петербург' ) !== false) return true;
            if ($courier_city == 'спб') return true;
            if ($courier_city == 'питер') return true;
        }
        
        return $zipcode_data[ 'is_spb' ];
    }
    
    private function get_zipcode_value()
    {
        $courier_zipcode = $this->registry[ 'CL_storage' ]->get_storage( 'courier_zipcode' );
        if ($courier_zipcode) return $courier_zipcode;
        
        return ($this->registry[ 'userdata' ])
            ? $this->registry[ 'userdata' ][ 'zip_code' ]
            : false;
    }
    
    public function calculate_costs($data)
    {
        $zipcode = $this->get_zipcode_value();
        $zipcode_data = Front_Order_Data_Delivery_Zipcode::get_zipcode_data( $zipcode );
        
        $is_spb = $this->is_spb( $zipcode_data );
        
        if (!$is_spb) {
            $output = array(
                'sum' => false,
                'is_spb' => false,
                'is_zipcode' => (!!$zipcode),
            );
        } else {
            $costs = ($data[ 'sum' ] >= FREE_DELIVERY_SUM) ? 0 : COURIER_SPB_COST;
            
            $output = array(
                'sum' => $costs,
                'is_spb' => true,
                'is_zipcode' => (!!$zipcode),
            );
        }
        
        $data[ 'costs' ][ 'courier' ] = $output;
        
        return $data;
    }
}

