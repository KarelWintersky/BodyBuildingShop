<?php

class Front_Order_Data_Steps
{
    
    public static function get_steps($step_id = false)
    {
        $steps = array(
            1 => array( 'Корзина', 'order' ),
            2 => array( 'Выбор доставки', 'order/delivery' ),
            3 => array( 'Выбор оплаты', 'order/payment' ),
            4 => array( 'Проверка', 'order/check' ),
        );
        
        return (!$step_id)
            ? $steps
            : $steps[ $step_id ];
    }
    
}

