<?php

class Adm_Orders_List extends Common_Rq
{
    
    private $registry;
    private $paging = 100;
    
    private $Adm_Orders_Search;
    private $Adm_Pagination;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Adm_Orders_Search = new Adm_Orders_Search( $this->registry );
        $this->Adm_Pagination = new Adm_Pagination( $this->registry, $_SERVER[ 'REQUEST_URI' ] );
    }
    
    public function do_page()
    {
        $vars = array(
            'search' => $this->Adm_Orders_Search->do_block(),
            'list' => $this->print_list(),
            'pages' => $this->Adm_Pagination->print_paging(),
        );
        
        foreach ($vars as $k => $v) $this->registry[ 'CL_template_vars' ]->set( $k, $v );
    }
    
    private function mk_conditions()
    {
        $q = array();
        
        if (isset( $_GET[ 'num' ] ) && $_GET[ 'num' ]) {
            $num = explode( '/', $_GET[ 'num' ] );
            
            $q[] = (count( $num ) == 3)
                ? sprintf( "id = '%d' AND user_num = '%d' AND payment_method = '%s'",
                    $num[ 0 ],
                    $num[ 1 ],
                    str_replace( 'H', 'Н', $num[ 2 ] )
                )
                : sprintf( "id = '%s' OR user_num = '%s' OR payment_method = '%s'",
                    $_GET[ 'num' ],
                    $_GET[ 'num' ],
                    $_GET[ 'num' ]
                );
        }
        
        if (isset( $_GET[ 'status' ] ) && $_GET[ 'status' ])
            $q[] = sprintf( "status = '%s'", $_GET[ 'status' ] );
        
        if (isset( $_GET[ 'date_from' ] ) && $_GET[ 'date_from' ])
            $q[] = sprintf( "DATE(made_on) >= DATE('%s')",
                date( 'Y-m-d', strtotime( $_GET[ 'date_from' ] ) )
            );
        
        if (isset( $_GET[ 'date_to' ] ) && $_GET[ 'date_to' ])
            $q[] = sprintf( "DATE(made_on) <= DATE('%s')",
                date( 'Y-m-d', strtotime( $_GET[ 'date_to' ] ) )
            );
        
        return (count( $q ))
            ? sprintf( "WHERE %s",
                implode( " AND ", $q )
            )
            : false;
    }
    
    private function mk_pagination()
    {
        $page = (isset( $_GET[ 'page' ] )) ? $_GET[ 'page' ] : 1;
        $offset = $this->paging * ($page - 1);
        
        return sprintf( "LIMIT %d, %d", $offset, $this->paging );
    }
    
    public function print_list()
    {
        $qLnk = mysql_query( sprintf( "
				SELECT SQL_CALC_FOUND_ROWS
					*
				FROM
					orders
				%s
				ORDER BY
					made_on DESC
				%s;
				",
            $this->mk_conditions(),
            $this->mk_pagination()
        ) );
        
        $this->Adm_Pagination->set_params(
            mysql_result( mysql_query( "SELECT FOUND_ROWS()" ), 0 ),
            $this->paging
        );
        
        $html = array();
        while ($o = mysql_fetch_assoc( $qLnk )) {
            $o[ 'num' ] = sprintf( '%d/%d/%s',
                $o[ 'id' ],
                $o[ 'user_num' ],
                $o[ 'payment_method' ]
            );
            
            $o[ 'lnk' ] = sprintf( '%d-%d-%s',
                $o[ 'id' ],
                $o[ 'user_num' ],
                $o[ 'payment_method' ]
            );
            
            $o[ 'status_name' ] = Adm_Orders_Helper::get_statuses( $o[ 'status' ] );
            
            $delivery = Front_Order_Data_Delivery::get_methods( $o[ 'delivery_type' ] );
            $o[ 'delivery_name' ] = (isset( $delivery[ 'name' ] )) ? mb_strtolower( $delivery[ 'name' ], 'utf-8' ) : false;
            
            $o[ 'payment_string' ] = $this->payment_string( $o );
            
            //чтобы отличить заказы по старой схеме от заказов по новой схеме
            //смотрим содержимое поля payment_method_id, как появившегося только в новой версии заказов
            $o[ 'sum_to_print' ] = ($o[ 'payment_method_id' ]) ? $o[ 'sum_with_discount' ] : $o[ 'sum' ];
            $o[ 'overall_to_print' ] = ($o[ 'payment_method_id' ]) ? $o[ 'overall_sum' ] : $o[ 'overall_price' ];
            
            $html[] = $this->do_rq( 'item', $o, true );
        }
        
        return (count( $html ))
            ? implode( '', $html )
            : $this->do_rq( 'empty', NULL, true );
    }
    
    private function payment_string($order)
    {
        if (!$order[ 'payment_method_id' ]) return $order[ 'payment_method' ];
        
        $payment = Front_Order_Data_Payment::get_methods( $order[ 'payment_method_id' ] );
        
        $extra_payment = ($order[ 'account_extra_payment' ])
            ? Front_Order_Data_Payment::get_methods( $order[ 'account_extra_payment' ] )
            : false;
        
        $extra_payment = ($extra_payment) ? $extra_payment[ 'short_name' ] : false;
        
        return sprintf( '%s <span>(%s%s)</span>',
            $order[ 'payment_method' ],
            mb_strtolower( $payment[ 'short_name' ], 'utf-8' ),
            ($extra_payment) ? sprintf( ', %s', mb_strtolower( $extra_payment, 'utf-8' ) ) : false
        );
    }
    
}

