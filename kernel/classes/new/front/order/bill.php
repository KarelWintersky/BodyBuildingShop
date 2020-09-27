<?php

class Front_Order_Bill extends Common_Rq
{
    
    private $registry;
    
    private $Front_Order_Bill_Cart;
    private $Front_Order_Bill_Account;
    private $Front_Order_Bill_Num;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Order_Bill_Cart = new Front_Order_Bill_Cart( $this->registry );
        $this->Front_Order_Bill_Account = new Front_Order_Bill_Account( $this->registry );
        $this->Front_Order_Bill_Num = new Front_Order_Bill_Num( $this->registry );
    }
    
    private function get_data($num, $skip_user_match)
    {
        $num = explode( '/', $num );
        if (count( $num ) != 3) return false;
        
        return ($num[ 2 ] == 'A' || $num[ 2 ] == 'А')
            ? $this->Front_Order_Bill_Account->get_data( $num, $skip_user_match )
            : $this->Front_Order_Bill_Cart->get_data( $num, $skip_user_match );
    }
    
    public function print_bill($num, $skip_user_match = false, $to_pdf = false)
    {
        $data = $this->get_data( $num, $skip_user_match );
        if (!$data) return false;
        
        $data[ 'to_pdf' ] = $to_pdf;
        
        $line = $this->do_rq( 'line', $data, true );
        
        $a = array(
            'lines' => $line.$line,
            'to_pdf' => $to_pdf,
        );
        
        return $this->do_rq( 'bill', $a );
    }
    
    public function to_screen()
    {
        $num = $this->Front_Order_Bill_Num->get_num();
        
        if ($num) {
            $html = $this->print_bill( $num[ 'num' ], $num[ 'skip_user_match' ] );
            if ($html) {
                echo $html;
                exit();
            }
        }
        
        echo 'Квитанция недоступна';
        exit();
    }
    
    public function to_letter($num)
    {
        return $this->print_bill( $num, true, true );
    }
    
}

?>