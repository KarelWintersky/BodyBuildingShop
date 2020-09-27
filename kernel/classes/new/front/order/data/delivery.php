<?php

class Front_Order_Data_Delivery
{
    
    public static function get_methods($method_id = false)
    {
        $methods = array(
            1 => array(
                'name' => 'Доставка по почте',
                'tech_name' => 'почтой',
                'payment' => array( 1, 2, 3, 4, 6, 7 ),
                'field' => 'delivery_mail',
                'class_alias' => 'post',
            ),
            2 => array(
                'name' => 'Доставка курьером',
                'tech_name' => 'курьером',
                'payment' => array( 2, 3, 4, 5, 6, 7 ),
                'field' => 'delivery_courier',
                'class_alias' => 'courier',
            ),
            /*3 => array(
             'name' => 'Доставка транспортной компанией',
                    'payment' => array(2,3,4,6,7)
            ),*/
            4 => array(
                'name' => 'Самовывоз',
                'tech_name' => 'самовывоз',
                'payment' => array( 2, 3, 4, 5, 6, 7 ),
                'field' => 'delivery_self',
                'class_alias' => 'self',
            ),
        );
        
        return (!$method_id)
            ? $methods
            : ((isset( $methods[ $method_id ] )) ? $methods[ $method_id ] : false);
    }
    
}

