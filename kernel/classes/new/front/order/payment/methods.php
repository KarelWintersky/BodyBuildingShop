<?php

class Front_Order_Payment_Methods
{
    
    /*
     * тут формируется список способов оплаты
    * в зависимости от уровня авторизации покупателя
    * и выбранного способа доставки
    * */
    
    private $registry;
    
    private $Front_Order_Storage;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Order_Storage = new Front_Order_Storage( $this->registry );
    }
    
    private function get_delivery_match()
    {
        $active = $this->Front_Order_Storage->get_storage( 'delivery' );
        $delivery = Front_Order_Data_Delivery::get_methods( $active );
        
        return $delivery[ 'payment' ];
    }
    
    private function correct_list($list, $data)
    {
        foreach ($list as $id => $arr) if ($arr[ 'disabled' ]) $list[ $id ][ 'active' ] = false;
        
        //закрываем наложку, если есть соответствующий параметр
        if (!$data[ 'nalog_payment_available' ]) {
            $list[ 1 ][ 'disabled' ] = true;
            $list[ 1 ][ 'active' ] = false;
        }
        
        //закрываем оплату курьеру наличными, если человек не из спб
        if ($this->Front_Order_Storage->get_storage( 'delivery' ) == 2 && !$data[ 'costs' ][ 'courier' ][ 'is_spb' ]) {
            $list[ 5 ][ 'disabled' ] = true;
            $list[ 5 ][ 'active' ] = false;
        }
        
        //закрываем оплату с личного счета, если нужно
        if (!$data[ 'account_payment_available' ]) {
            $list[ 6 ][ 'disabled' ] = true;
            $list[ 6 ][ 'active' ] = false;
        }
        
        $is_active = false;
        foreach ($list as $l) if ($l[ 'active' ]) $is_active = true;
        
        if (!$is_active)
            foreach ($list as $id => $arr) {
                if (!$arr[ 'disabled' ]) {
                    $list[ $id ][ 'active' ] = true;
                    break;
                }
            }
        
        return $list;
    }
    
    public function get_actual_list($data)
    {
        $active = $this->Front_Order_Storage->get_storage( 'payment' );
        $active = ($active) ? $active : 1;
        
        $match = $this->get_delivery_match();
        
        $methods = Front_Order_Data_Payment::get_methods();
        
        $list = array();
        foreach ($methods as $id => $arr) {
            if (isset( $arr[ 'dont_display_on_frontend' ] )) continue;
            
            $list[ $id ] = array(
                'id' => $id,
                'name' => $arr[ 'name' ],
                'active' => ($id == $active),
                'disabled' => ($id == 1 || !in_array( $id, $match )), //jan 2016 - закрыли оплату наложенным платежом
                'class_alias' => (isset( $arr[ 'class_alias' ] )) ? $arr[ 'class_alias' ] : false,
            );
        }
        
        $list = $this->correct_list( $list, $data );
        
        return $list;
    }
    
}

