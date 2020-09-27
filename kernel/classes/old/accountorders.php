<?php

class Accountorders
{
    
    private $registry;
    public $statuses;
    
    public function __construct($registry, $frompage = true)
    {
        $this->registry = $registry;
        $this->registry->set( 'accountorders', $this );
        
        $this->p_opts = array( 20, 50, 100 );
        
        $this->statuses = array(
            1 => 'сформирован',
            2 => 'оплачен',
            3 => 'отменен',
        );
        
        if ($frompage) {
            $route = $this->registry[ 'aias_path' ];
            array_shift( $route );
            
            if (count( $route ) == 0) {
                $this->registry[ 'f_404' ] = false;
                $this->registry[ 'template' ]->set( 'c', 'accountorders/main' );
            }
        }
        
    }
    
    private function item_rq($name, $a = NULL)
    {
        require($this->registry[ 'template' ]->TF.'item/accountorders/'.$name.'.html');
    }
    
    public function statuses_options($cur, $all = false)
    {
        $statuses = ($all) ? array( 0 => 'все' ) : array();
        foreach ($this->statuses as $id => $name) {
            $statuses[ $id ] = $name;
        }
        
        foreach ($statuses as $id => $name) {
            $sel = ($id == $cur) ? 'selected' : '';
            echo '<option '.$sel.' value="'.$id.'">'.$name.'</option>';
        }
    }
    
    private function srch_string()
    {
        $q_arr = array();
        if (isset( $_GET[ 'status' ] ) && $_GET[ 'status' ] != 0) {
            $q_arr[] = "account_orders.status = '".$_GET[ 'status' ]."'";
        }
        
        if (isset( $_GET[ 'id' ] ) && $_GET[ 'id' ] != '') {
            $num_arr = explode( '/', $_GET[ 'id' ] );
            $q_arr[] = "account_orders.id = '".$num_arr[ 0 ]."'";
        }
        
        return (count( $q_arr ) > 0) ? "WHERE ".implode( " AND ", $q_arr ) : '';
    }
    
    public function pagination_options()
    {
        $html = '';
        foreach ($this->p_opts as $opt) {
            $sel = (isset( $_COOKIE[ 'adm_orders_paging' ][ 2 ] ) && $_COOKIE[ 'adm_orders_paging' ][ 2 ] == $opt) ? 'selected' : '';
            
            $html .= '<option '.$sel.' value="'.$opt.'">'.$opt.'</option>';
        }
        
        echo $html;
    }
    
    private function mk_pagination()
    {
        $PAGING = (isset( $_COOKIE[ 'adm_orders_paging' ][ 2 ] )) ? $_COOKIE[ 'adm_orders_paging' ][ 2 ] : 20;
        
        $page = (isset( $_GET[ 'page' ] )) ? $_GET[ 'page' ] : 1;
        $offset = $PAGING * ($page - 1);
        
        $this->registry[ 'orders_paging' ] = $PAGING;
        $this->registry[ 'orders_display_first' ] = $offset + 1;
        $this->registry[ 'orders_display_last' ] = $offset + $PAGING;
        
        return "LIMIT ".$offset.", ".$PAGING;
    }
    
    public function get_orders_list(&$list)
    {
        
        $q_str = $this->srch_string();
        
        $q_amount = $this->mk_pagination();
        
        $qLnk = mysql_query( "
							SELECT SQL_CALC_FOUND_ROWS
								account_orders.*,
								users.name AS user_name
							FROM
								account_orders
							LEFT OUTER JOIN users ON users.id = account_orders.user_id
							".$q_str."
							ORDER BY
								account_orders.createdon DESC
							".$q_amount.";
							" );
        $qA = mysql_query( "SELECT FOUND_ROWS();" );
        $this->registry[ 'orders_amount' ] = mysql_result( $qA, 0 );
        
        ob_start();
        while ($o = mysql_fetch_assoc( $qLnk )) {
            $o[ 'num' ] = $o[ 'id' ].'/'.$o[ 'user_num' ].'/А';
            $this->item_rq( 'line', $o );
        }
        $list = ob_get_contents();
        ob_end_clean();
    }
    
    public function pagination()
    {
        $pages_amount = ceil( $this->registry[ 'orders_amount' ] / $this->registry[ 'orders_paging' ] );
        $cur_page = (isset( $_GET[ 'page' ] )) ? $_GET[ 'page' ] : 1;
        if ($pages_amount > 1) {
            ob_start();
            for ($i = 1; $i <= $pages_amount; $i++) {
                $a[ 'num' ] = $i;
                $a[ 'lnk' ] = $this->get_pagination_link( $i );
                $a[ 'active' ] = ($i == $cur_page) ? 'active' : '';
                $this->item_rq( 'orders_paging', $a );
            }
            $html = ob_get_contents();
            ob_end_clean();
            echo '<ul id="orders_paging">'.$html.'</ul><div id="orders_paginig_overall">Показаны заказы с <b>'.$this->registry[ 'orders_display_first' ].'</b> по <b>'.$this->registry[ 'orders_display_last' ].'</b> из <b>'.$this->registry[ 'orders_amount' ].'</b></div>';
        }
    }
    
    private function get_pagination_link($i)
    {
        $url_arr = explode( '?', $_SERVER[ 'REQUEST_URI' ] );
        
        if (isset( $url_arr[ 1 ] )) {
            $params = explode( '&', $url_arr[ 1 ] );
            foreach ($params as $key => $p) {
                $p_arr = explode( '=', $p );
                if ($p_arr[ 0 ] == 'page') {
                    unset( $params[ $key ] );
                }
            }
            $new_url = $url_arr[ 0 ].'?'.implode( '&', $params );
            $delim = '&';
        } else {
            $new_url = $url_arr[ 0 ];
            $delim = '?';
        }
        
        return ($i == 1) ? $new_url : $new_url.$delim.'page='.$i;
    }
    
    public function sav()
    {
        
        foreach ($_POST[ 'order' ] as $id => $arr) {
            if ($arr[ 'new' ] != $arr[ 'old' ]) {
                mysql_query( "UPDATE account_orders SET account_orders.status = '".$arr[ 'new' ]."' WHERE account_orders.id = '".$id."';" );
                if ($arr[ 'new' ] == 3) {
                    $this->send_user_cancellation( $arr );
                } elseif ($arr[ 'new' ] == 2) {
                    $this->user_increase_account( $arr );
                    $this->send_user_confirm( $arr );
                }
            }
        }
        
    }
    
    public function cancel_expired()
    {
        
        $cancelled = array();
        $qLnk = mysql_query( "
							SELECT
								account_orders.id,
								account_orders.user_num,
								account_orders.createdon,
								account_orders.user_id
							FROM
								account_orders
							WHERE
								DATE(account_orders.createdon) < DATE_SUB(DATE(NOW()),INTERVAL ".ACCOUNT_ORDERS_EXPIRE." DAY)
								AND
								account_orders.status = 1								
							" );
        while ($o = mysql_fetch_assoc( $qLnk )) {
            $cancelled[] = $o;
        }
        
        mysql_query( "
					UPDATE
						account_orders
					SET
						account_orders.status = 3
					WHERE
						DATE(account_orders.createdon) < DATE_SUB(DATE(NOW()),INTERVAL ".ACCOUNT_ORDERS_EXPIRE." DAY)
						AND
						account_orders.status = 1
					" );
        
        foreach ($cancelled as $arr) {
            $this->send_user_cancellation( $arr );
        }
        
    }
    
    public function send_user_cancellation($arr)
    {
        
        $qLnk = mysql_query( "
							SELECT
								users.name,
								users.email
							FROM
								users
							WHERE
								users.id = '".$arr[ 'user_id' ]."'
							LIMIT 1;
							" );
        if (mysql_num_rows( $qLnk ) > 0) {
            $u = mysql_fetch_assoc( $qLnk );
            
            $replace_arr = array(
                'USER_NAME' => $u[ 'name' ],
                'ORDER_DATE' => date( 'd.m.Y', strtotime( $arr[ 'createdon' ] ) ),
                'ORDER_NUM' => $arr[ 'id' ].'/'.$arr[ 'user_num' ].'/А',
            );
            
            $mailer = new Mailer( $this->registry, 23, $replace_arr, $u[ 'email' ] );
            
        }
        
    }
    
    public function user_increase_account($arr)
    {
        mysql_query( "
					UPDATE
						users
					SET
						users.my_account = (users.my_account + ".$arr[ 'sum' ].")
					WHERE
						users.id = '".$arr[ 'user_id' ]."';
					" );
    }
    
    public function send_user_confirm($arr)
    {
        $qLnk = mysql_query( "
							SELECT
								users.name,
								users.email
							FROM
								users
							WHERE
								users.id = '".$arr[ 'user_id' ]."'
							LIMIT 1;
							" );
        if (mysql_num_rows( $qLnk ) > 0) {
            $u = mysql_fetch_assoc( $qLnk );
            
            $replace_arr = array(
                'USER_NAME' => $u[ 'name' ],
                'SUM' => $arr[ 'sum' ],
                'ORDER_DATE' => date( 'd.m.Y', strtotime( $arr[ 'createdon' ] ) ),
                'ORDER_NUM' => $arr[ 'id' ].'/'.$arr[ 'user_num' ].'/А',
            );
            
            $mailer = new Mailer( $this->registry, 22, $replace_arr, $u[ 'email' ] );
            
        }
    }
    
}
