<?php

class Common_Useful_Date
{
    
    private static function mkstring($date)
    {
        $date = (is_numeric( $date )) ? $date : strtotime( $date );
        
        return $date;
    }
    
    public static function date2node($date, $type)
    {
        $date = self::mkstring( $date );
        
        //28 февраля 2014
        if ($type == 1)
            return sprintf( '%d %s %d',
                date( 'd', $date ),
                self::date2monthname( $date, 1 ),
                date( 'Y', $date )
            );
        
        //февраль 2014
        if ($type == 2)
            return sprintf( '%s %d',
                self::date2monthname( $date ),
                date( 'Y', $date )
            );
    }
    
    public static function date2dayofweek($date, $t)
    {
        $date = self::mkstring( $date );
        
        $days = array(
            0 => array( 'вс', 'воскресение' ),
            1 => array( 'пн', 'понедельник' ),
            2 => array( 'вт', 'вторник' ),
            3 => array( 'ср', 'среда' ),
            4 => array( 'чт', 'четверг' ),
            5 => array( 'пт', 'пятница' ),
            6 => array( 'сб', 'суббота' ),
        );
        return $days[ date( 'w', $date ) ][ $t ];
    }
    
    public static function date2monthname($date, $key = 0)
    {
        $date = self::mkstring( $date );
        
        $captions = array(
            1 => array( 'январь', 'января' ),
            2 => array( 'февраль', 'февраля' ),
            3 => array( 'март', 'марта' ),
            4 => array( 'апрель', 'апреля' ),
            5 => array( 'май', 'мая' ),
            6 => array( 'июнь', 'июня' ),
            7 => array( 'июль', 'июля' ),
            8 => array( 'август', 'августа' ),
            9 => array( 'сентябрь', 'сентября' ),
            10 => array( 'октябрь', 'октября' ),
            11 => array( 'ноябрь', 'ноября' ),
            12 => array( 'декабрь', 'декабря' ),
        );
        return $captions[ date( 'n', $date ) ][ $key ];
    }
    
}

