<?php

class Adm_News_Types
{
    
    public static function get_types($key = false)
    {
        $types = array(
            1 => array( 'Сайта', '#b00b2f', 'site' ),
            2 => array( 'Спортивного питания', '#003e71', 'food' ),
        );
        
        return ($key)
            ? $types[ $key ]
            : $types;
    }
}

