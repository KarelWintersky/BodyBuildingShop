<?php

class Front_Profile_Orders_Account_Make
{
    
    private $registry;
    
    private $Front_Order_Bill;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Order_Bill = new Front_Order_Bill( $this->registry );
    }
    
    public function send_order($order_num)
    {
        $html = $this->Front_Order_Bill->print_bill( $order_num, false, true );
        
        $pdfmanager = new Pdfmanager( $this->registry );
        $attach_string = $pdfmanager->fileCompose( $html );
        
        $replace = array(
            'USER_NAME' => $this->registry[ 'userdata' ][ 'name' ],
            'ORDER_NUM' => $order_num,
        );
        
        $mailer = new Mailer( $this->registry, 25, $replace, $this->registry[ 'userdata' ][ 'email' ], $attach_string );
    }
    
}

