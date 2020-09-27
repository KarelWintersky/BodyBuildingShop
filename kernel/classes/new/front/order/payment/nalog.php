<?php

class Front_Order_Payment_Nalog extends Common_Rq
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    public function do_text($data)
    {
        $arr = $data[ 'costs' ][ 'post' ];
        
        /*Н/П недоступен – выбран другой вариант доставки - не почта*/
        if ($this->registry[ 'CL_storage' ]->get_storage( 'delivery' ) != 1)
            $type = 1;
        
        /*Н/П доступен*/
        elseif ($data[ 'nalog_payment_available' ])
            $type = 2;
        
        /*Н/П недоступен – труднодоступный регион*/
        elseif (!$data[ 'nalog_payment_available' ] && $this->registry[ 'userdata' ] && isset( $arr[ 'hard_cost' ] ) && $arr[ 'hard_cost' ])
            $type = 4;
        
        /*Н/П недоступен – превышение макс.суммы*/
        elseif (!$data[ 'nalog_payment_available' ] && $this->registry[ 'userdata' ] && $this->registry[ 'userdata' ][ 'max_nalog' ] < $data[ 'sum_with_discount' ])
            $type = 3;
        
        /*Н/П недоступен – индекс не найден в базе данных индексов*/
        elseif (!isset( $arr[ 'post_available' ] ) && !$arr[ 'no_zip_code' ])
            $type = 5;
        
        /*Н/П недоступен – индекс у покупателя вообще не указан*/
        elseif ($this->registry[ 'userdata' ] && !isset( $arr[ 'post_available' ] ) && $arr[ 'no_zip_code' ])
            $type = 6;
        
        /*Н/П недоступен – незарегистрированный клиент*/
        elseif (!$this->registry[ 'userdata' ])
            $type = 7;
        
        $a = array(
            'type' => (isset( $type )) ? $type : false,
            'cost' => Common_Useful::price2read( $data[ 'nalog' ] ),
            'max_nalog' => ($this->registry[ 'userdata' ]) ? Common_Useful::price2read( $this->registry[ 'userdata' ][ 'max_nalog' ] ) : false,
        );
        
        return $this->do_rq( 'text', $a );
    }
    
}

