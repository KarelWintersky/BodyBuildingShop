<?php

class Front_Order_Check_Payment extends Common_Rq
{
    
    /*
     * сообщение при доплате
     * при оплате со счета и нехватке средств
     * */
    
    private $registry;
    
    private $Front_Order_Payment_Methods;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Order_Payment_Methods = new Front_Order_Payment_Methods( $this->registry );
    }
    
    private function print_methods($data)
    {
        $allowed_methods = array( 2, 3, 4, 7 );
        
        $methods = $this->Front_Order_Payment_Methods->get_actual_list( $data );
        foreach ($methods as $key => $m)
            if (!in_array( $key, $allowed_methods ) || $m[ 'disabled' ])
                unset( $methods[ $key ] );
        
        $html = array();
        $i = 1;
        foreach ($methods as $m) {
            
            $a = array(
                'id' => $m[ 'id' ],
                'name' => $m[ 'name' ],
                'checked' => ($i == 1) ? 'checked' : '',
            );
            
            $html[] = $this->do_rq( 'method', $a, true );
            
            $i++;
        }
        
        return implode( '', $html );
    }
    
    public function do_block($data)
    {
        $payment = $this->registry[ 'CL_storage' ]->get_storage( 'payment' );
        if ($payment != 6 || !$this->registry[ 'userdata' ]) return false;
        
        $sum = $data[ 'sum_with_discount' ] + $data[ 'delivery_sum' ];
        $diff = $sum - $this->registry[ 'userdata' ][ 'my_account' ];
        if ($diff <= 0) return false;
        
        $a = array(
            'diff' => Common_Useful::price2read( $diff ),
            'methods' => $this->print_methods( $data ),
        );
        
        return $this->do_rq( 'block', $a );
    }
    
    
}

