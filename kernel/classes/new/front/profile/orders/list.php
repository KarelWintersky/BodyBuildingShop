<?php

class Front_Profile_Orders_List extends Common_Rq
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    private function get_data($user)
    {
        $orders = array();
        $qLnk = mysql_query( sprintf( "
				SELECT
					*
				FROM
					orders
				WHERE
					user_id = '%d'
				ORDER BY
					id DESC;
				",
            $user[ 'id' ]
        ) );
        while ($o = mysql_fetch_assoc( $qLnk )) $orders[ $o[ 'status' ] ][ $o[ 'ai' ] ] = $o;
        
        return $orders;
    }
    
    private function orders_lines($orders)
    {
        ksort( $orders, SORT_NUMERIC );
        $orders = array_reverse( $orders );
        
        $html = array();
        foreach ($orders as $o) {
            
            $o[ 'num' ] = sprintf( '%d/%d/%s',
                $o[ 'id' ],
                $o[ 'user_num' ],
                $o[ 'payment_method' ]
            );
            $o[ 'link' ] = sprintf( '%d-%d-%s',
                $o[ 'id' ],
                $o[ 'user_num' ],
                $o[ 'payment_method' ]
            );
            
            $o[ 'classes' ] = ($o[ 'status' ] == 4 || $o[ 'status' ] == 5) ? 'cancelled' : '';
            
            $o[ 'price' ] = ($o[ 'payment_method_id' ]) ? $o[ 'overall_sum' ] : $o[ 'overall_price' ];
            
            $o[ 'discount' ] = ($o[ 'payment_method_id' ]) ? $o[ 'discount_percent' ] : $o[ 'discount' ];
            
            $html[] = $this->do_rq( 'item', $o, true );
        }
        
        return implode( '', $html );
    }
    
    private function account_orders()
    {
        $statuses = array(
            1 => 'сформирован',
            2 => 'оплачен',
            3 => 'отменен',
        );
        
        $html = array();
        $qLnk = mysql_query( sprintf( "
				SELECT
					*
				FROM
					account_orders
				WHERE
					user_id = '%d'
				ORDER BY
					createdon DESC;
				",
            $this->registry[ 'userdata' ][ 'id' ]
        ) );
        while ($o = mysql_fetch_assoc( $qLnk )) {
            $o[ 'num' ] = sprintf( '%d/%d/A',
                $o[ 'id' ],
                $o[ 'user_num' ]
            );
            $o[ 'status_name' ] = $statuses[ $o[ 'status' ] ];
            
            $html[] = $this->do_rq( 'account', $o, true );
        }
        
        return (count( $html ))
            ? implode( '', $html )
            : $this->do_rq( 'blank', NULL, true );
    }
    
    private function print_orders($orders)
    {
        $output = array();
        $statuses = range( 1, 5 );
        
        foreach ($statuses as $status) {
            if (isset( $orders[ $status ] )) {
                $output[ $status ] = $this->orders_lines( $orders[ $status ] );
            } elseif ($status != 5) {
                $output[ $status ] = $this->do_rq( 'blank', NULL, true );
            }
            
        }
        
        $output[ 'account' ] = $this->account_orders();
        
        return $output;
    }
    
    public function print_list($user)
    {
        $orders = $this->get_data( $user );
        
        $vars = $this->print_orders( $orders );
        
        $this->registry[ 'CL_template_vars' ]->set( 'list',
            $this->do_rq( 'orders', $vars )
        );
    }
    
}

