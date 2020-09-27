<?php

class Front_Profile_Orders_Page extends Common_Rq
{
    
    private $registry;
    
    private $Front_Profile_Orders_Page_Old;
    private $Front_Profile_Orders_Page_New;
    private $Front_Profile_Orders_Page_Goods;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Profile_Orders_Page_Old = new Front_Profile_Orders_Page_Old( $this->registry );
        $this->Front_Profile_Orders_Page_New = new Front_Profile_Orders_Page_New( $this->registry );
        $this->Front_Profile_Orders_Page_Goods = new Front_Profile_Orders_Page_Goods( $this->registry );
    }
    
    public function check_order($num)
    {
        $arr = explode( '-', $num );
        if (count( $arr ) != 3) return false;
        
        $qLnk = mysql_query( sprintf( "
				SELECT
					*
				FROM
					orders
				WHERE
					id = '%d'
					AND
					user_num = '%d'
					AND
					payment_method = '%s'
					AND
					user_id = '%d'
				",
            $arr[ 0 ],
            $arr[ 1 ],
            mysql_real_escape_string( $arr[ 2 ] ),
            $this->registry[ 'userdata' ][ 'id' ]
        ) );
        $order = mysql_fetch_assoc( $qLnk );
        if (!$order) return false;
        
        $order[ 'num' ] = sprintf( '%d/%d/%s',
            $order[ 'id' ],
            $order[ 'user_num' ],
            $order[ 'payment_method' ]
        );
        
        $delivery = Front_Order_Data_Delivery::get_methods( $order[ 'delivery_type' ] );
        $order[ 'delivery_name' ] = $delivery[ 'name' ];
        
        $order = ($order[ 'payment_method_id' ])
            ? $this->Front_Profile_Orders_Page_New->do_extend( $order )
            : $this->Front_Profile_Orders_Page_Old->do_extend( $order );
        
        $this->set_vars( $order );
        
        return true;
    }
    
    private function set_vars($order)
    {
        
        $this->registry[ 'longtitle' ] = sprintf( 'Заказ № %s', $order[ 'num' ] );
        
        $vars = array(
            'num' => $order[ 'num' ],
            'features' => $this->do_rq( 'features', $order ),
            'numbers' => $this->do_rq( 'numbers', $order[ 'numbers' ] ),
            'goods' => $this->Front_Profile_Orders_Page_Goods->print_goods( $order ),
        );
        
        foreach ($vars as $k => $v) $this->registry[ 'CL_template_vars' ]->set( $k, $v );
    }
    
}

