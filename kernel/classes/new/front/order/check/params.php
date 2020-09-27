<?php

class Front_Order_Check_Params extends Common_Rq
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    public function do_params($data)
    {
        
        $a = array(
            'delivery_payment' => $this->delivery_payment( $data ),
            'personal_data' => $this->personal_data( $data ),
            'personal_data_h' => ($this->registry[ 'CL_storage' ]->get_storage( 'delivery' ) == 1)
                ? 'Проверьте еще раз реквизиты, по которым будет отправлен заказ'
                : 'Ваши контактные данные',
        );
        
        return $this->do_rq( 'params', $a );
    }
    
    private function get_address($data, $delivery)
    {
        if ($delivery == 4) return false;
        
        if ($delivery == 2) {
            $house = $this->registry[ 'CL_storage' ]->get_storage( 'courier_house' );
            
            $address = array(
                $this->registry[ 'CL_storage' ]->get_storage( 'courier_zipcode' ),
                $this->registry[ 'CL_storage' ]->get_storage( 'courier_city' ),
                $this->registry[ 'CL_storage' ]->get_storage( 'courier_street' ),
                ($house) ? sprintf( 'д. %s', $house ) : false,
            );
        } elseif ($this->registry[ 'userdata' ]) {
            $address = array(
                $this->registry[ 'userdata' ][ 'zip_code' ],
                $this->registry[ 'userdata' ][ 'city' ],
                $this->registry[ 'userdata' ][ 'street' ],
                ($this->registry[ 'userdata' ][ 'house' ])
                    ? sprintf( 'д. %s', $this->registry[ 'userdata' ][ 'house' ] )
                    : false,
                ($this->registry[ 'userdata' ][ 'corpus' ])
                    ? sprintf( 'корп. %s', $this->registry[ 'userdata' ][ 'corpus' ] )
                    : false,
                ($this->registry[ 'userdata' ][ 'flat' ])
                    ? sprintf( 'кв. %s', $this->registry[ 'userdata' ][ 'flat' ] )
                    : false,
            );
        } else $address = false;
        
        if (!$address) return false;
        
        foreach ($address as $key => $val) if (!$val) unset( $address[ $key ] );
        
        return implode( ', ', $address );
    }
    
    private function personal_data($data)
    {
        $list = array();
        
        $delivery = $this->registry[ 'CL_storage' ]->get_storage( 'delivery' );
        
        $name = false;
        if ($delivery == 2)
            $name = $this->registry[ 'CL_storage' ]->get_storage( 'courier_name' );
        elseif ($delivery == 4)
            $name = $this->registry[ 'CL_storage' ]->get_storage( 'self_name' );
        if (!$name && $this->registry[ 'userdata' ]) $name = $this->registry[ 'userdata' ][ 'name' ];
        if ($name) {
            $list[] = array(
                'label' => 'Получатель',
                'text' => $name,
            );
        }
        
        $address = $this->get_address( $data, $delivery );
        if ($address) {
            $list[] = array(
                'label' => 'Почтовый адрес',
                'text' => $address,
            );
        }
        
        $email = false;
        if ($delivery == 2)
            $email = $this->registry[ 'CL_storage' ]->get_storage( 'courier_email' );
        elseif ($this->registry[ 'userdata' ])
            $email = $this->registry[ 'userdata' ][ 'email' ];
        
        if ($email) {
            $list[] = array(
                'label' => 'Email',
                'text' => $email,
            );
        }
        
        $phone = false;
        if ($delivery == 2)
            $phone = $this->registry[ 'CL_storage' ]->get_storage( 'courier_phone' );
        elseif ($delivery == 4)
            $phone = $this->registry[ 'CL_storage' ]->get_storage( 'self_phone' );
        if ($phone) {
            $list[] = array(
                'label' => 'Телефон',
                'text' => $phone,
            );
        }
        
        return $this->print_list( $list );
    }
    
    private function delivery_payment($data)
    {
        $list = array();
        
        $delivery = Front_Order_Data_Delivery::get_methods(
            $this->registry[ 'CL_storage' ]->get_storage( 'delivery' )
        );
        $list[] = array(
            'label' => 'Доставка',
            'text' => $delivery[ 'name' ],
        );
        
        $payment = Front_Order_Data_Payment::get_methods(
            $this->registry[ 'CL_storage' ]->get_storage( 'payment' )
        );
        $list[] = array(
            'label' => 'Оплата',
            'text' => $payment[ 'name' ],
        );
        
        return $this->print_list( $list );
    }
    
    private function print_list($items)
    {
        $html = array();
        
        foreach ($items as $i)
            $html[] = $this->do_rq( 'list', $i, true );
        
        return $this->do_rq( 'list',
            implode( '', $html )
        );
    }
}

