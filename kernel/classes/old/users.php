<?php

class Users
{
    
    private $registry;
    
    public function __construct($registry, $frompage = true)
    {
        $this->registry = $registry;
        $this->registry->set( 'users', $this );
        $accountorders = new Accountorders( $this->registry, false );
        
        if ($frompage) {
            $route = $this->registry[ 'aias_path' ];
            array_shift( $route );
            
            if (count( $route ) == 0) {
                $this->registry[ 'f_404' ] = false;
                $this->registry[ 'template' ]->set( 'c', 'users/main' );
            } elseif (count( $route ) == 1 && $this->user_check( $route[ 0 ] )) {
                $this->registry[ 'f_404' ] = false;
                $this->registry[ 'template' ]->set( 'c', 'users/user' );
            }
        }
        
    }
    
    private function item_rq($name, $a = NULL)
    {
        require($this->registry[ 'template' ]->TF.'item/users/'.$name.'.html');
    }
    
    private function user_check($id)
    {
        
        if (!is_numeric( $id )) {
            return false;
        }
        
        $qLnk = mysql_query( "
							SELECT
								users.*
							FROM
								users
							WHERE
								users.id = '".$id."'
							LIMIT 1;
							" );
        if (mysql_num_rows( $qLnk ) > 0) {
            $this->registry[ 'user_info' ] = mysql_fetch_assoc( $qLnk );
            return true;
        } else {
            return false;
        }
        
    }
    
    public function user_sav()
    {
        foreach ($_POST as $key => $val) {
            $$key = $val;
        }
        
        $get_news = (isset( $get_news )) ? 1 : 0;
        $get_catalog_changes = (isset( $get_catalog_changes )) ? 1 : 0;
        
        $q_pass = ($new_pass != '') ? $q_pass = ", users.pass = '".md5( $new_pass )."'" : "";
        
        mysql_query( "
					UPDATE
						users
					SET
						users.name = '".$name."',
						users.email = '".$email."',
						users.phone = '".$phone."',
						users.zip_code = '".$zip_code."',
						users.region = '".$region."',
						users.district = '".$district."',
						users.city = '".$city."',
						users.street = '".$street."',
						users.house = '".$house."',
						users.corpus = '".$corpus."',
						users.flat = '".$flat."',
						users.personal_discount = '".$personal_discount."',
						users.max_nalog = '".$max_nalog."',
						users.my_account = '".$my_account."',
						users.wishes = '".$wishes."',
						users.get_news = '".$get_news."',
						users.get_catalog_changes = '".$get_catalog_changes."'
						".$q_pass."
					WHERE
						users.id = '".$id."'
					" );
        
        $mail_nalog = ($max_nalog != $max_nalog_old) ? true : false;
        $mail_discount = ($personal_discount != $personal_discount_old) ? true : false;
        
        if ($mail_discount || $mail_nalog) {
            $this->mail_user_data_change( $mail_nalog, $mail_discount, $_POST );
        }
        
        if ($my_account_old < $my_account) {
            $differ = $my_account - $my_account_old;
            $this->mail_user_account_incr( $differ, $_POST );
        }
        
    }
    
    private function mail_user_account_incr($differ, $arr)
    {
        $replace_arr = array(
            'USER_NAME' => $arr[ 'name' ],
            'SUM' => $differ,
        );
        
        $mailer = new Mailer( $this->registry, 21, $replace_arr, $arr[ 'email' ] );
    }
    
    private function mail_user_data_change($mail_nalog, $mail_discount, $arr)
    {
        if ($mail_nalog && $mail_discount) {
            $tpl_id = 9;
        } elseif ($mail_nalog) {
            $tpl_id = 5;
        } elseif ($mail_discount) {
            $tpl_id = 8;
        }
        
        $replace_arr = array(
            'USER_NAME' => $arr[ 'name' ],
            'MAX_NALOG' => $arr[ 'max_nalog' ],
            'PERSONAL_DISCOUNT' => $arr[ 'personal_discount' ],
            'SITE_URL' => THIS_URL,
        );
        
        $mailer = new Mailer( $this->registry, $tpl_id, $replace_arr, $arr[ 'email' ] );
        
    }
    
    public function users_list()
    {
        
        $params = $this->srch_params();
        $params = (count( $params ) > 0) ? "WHERE ".implode( " AND ", $params ) : "";
        
        $qLnk = mysql_query( "
							SELECT
								users.*
							FROM
								users
							".$params."
							ORDER BY
								users.type DESC,
								users.name ASC
							" );
        $count = mysql_num_rows( $qLnk );
        $type = NULL;
        while ($u = mysql_fetch_assoc( $qLnk )) {
            
            $u[ 'type_change' ] = ($u[ 'type' ] != $type) ? true : false;
            
            $this->item_rq( 'list_item', $u );
            
            $type = $u[ 'type' ];
        }
        
        if ($count == 0) {
            $this->item_rq( 'list_not_found' );
        }
        
    }
    
    private function srch_params()
    {
        
        $q = array();
        
        if (isset( $_GET[ 'name' ] ) && trim( $_GET[ 'name' ] ) != '') {
            $q[] = "users.name LIKE '%".$_GET[ 'name' ]."%'";
        }
        
        if (isset( $_GET[ 'email' ] ) && trim( $_GET[ 'email' ] ) != '') {
            $q[] = "users.email LIKE '%".$this->parse_email( $_GET[ 'email' ] )."%'";
        }
        
        if (isset( $_GET[ 'login' ] ) && trim( $_GET[ 'login' ] ) != '') {
            $q[] = "users.login LIKE '%".$_GET[ 'login' ]."%'";
        }
        
        if (isset( $_GET[ 'name_FL' ] ) && trim( $_GET[ 'name_FL' ] ) != '') {
            $q[] = "LEFT(users.name,1) = '".$_GET[ 'name_FL' ]."'";
        }
        
        if (isset( $_GET[ 'login_FL' ] ) && trim( $_GET[ 'login_FL' ] ) != '') {
            $q[] = "LEFT(users.login,1) = '".$_GET[ 'login_FL' ]."'";
        }
        
        if (count( $q ) == 0) {
            $q[] = "users.type IN (1,2)";
        }
        
        return $q;
        
    }
    
    private function parse_email($email)
    {
        
        //$email = trim(trim($email,'<'),'>');
        
        if ((strpos( $email, '<' ) || strpos( $email, '<' ) == 0) && strpos( $email, '>' )) {
            $a1 = explode( '<', trim( $email ) );
            if (count( $a1 ) > 1) {
                $a2 = explode( '>', $a1[ 1 ] );
                $email = trim( $a2[ 0 ] );
            }
        }
        
        return trim( $email );
    }
    
    public function aplhabet($type)
    {
        $html = '';
        
        $eng_range = range( 'a', 'z' );
        $rus_range = range( '192', '223' );
        
        $i = 1;
        foreach ($eng_range as $letter) {
            $last = (count( $eng_range ) == $i) ? 'last' : '';
            $html .= '<a class="'.$last.'" href="/adm/users/?'.$type.'='.$letter.'">'.$letter.'</a>';
            $i++;
        }
        
        foreach ($rus_range as $code) {
            $letter = mb_strtolower( iconv( 'cp1251', 'utf-8', chr( $code ) ), 'utf-8' );
            $html .= '<a href="/adm/users/?'.$type.'='.$letter.'">'.$letter.'</a>';
        }
        
        echo '<div class="alphabet_list">'.$html.'</div>';
        
    }
    
    public function get_user_orders(&$orders)
    {
        $orders = array();
        
        $qLnk = mysql_query( "
								SELECT
									*,
									IF(payment_method_id>0,overall_sum,overall_price) AS overall_price
								FROM
									orders
								WHERE
									user_id = '".$this->registry[ 'user_info' ][ 'id' ]."'
								ORDER BY
									made_on DESC;
								" );
        while ($o = mysql_fetch_assoc( $qLnk )) {
            $orders[ $o[ 'status' ] ][] = $o;
        }
    }
    
    public function print_orders($orders, $type, $colspan)
    {
        $types = (is_array( $type )) ? $type : array( $type );
        
        $found_flag = false;
        
        foreach ($types as $t) {
            if (isset( $orders[ $t ] )) {
                $found_flag = true;
                foreach ($orders[ $t ] as $l) {
                    
                    $l[ 'num' ] = $l[ 'id' ].'/'.$l[ 'user_num' ].'/'.$l[ 'payment_method' ];
                    
                    $this->item_rq( 'order_tr', $l );
                }
            }
        }
        
        if (!$found_flag) echo '<td class="no_orders" colspan="'.$colspan.'">Нет ни одного заказа</td>';
        
    }
    
    public function account_orders()
    {
        
        $qLnk = mysql_query( "
							SELECT
								account_orders.*
							FROM
								account_orders
							WHERE
								account_orders.user_id = '".$this->registry[ 'user_info' ][ 'id' ]."'
							ORDER BY
								account_orders.createdon DESC;
							" );
        if (mysql_num_rows( $qLnk ) > 0) {
            while ($o = mysql_fetch_assoc( $qLnk )) {
                $o[ 'num' ] = $o[ 'id' ].'/'.$o[ 'user_num' ].'/А';
                $this->item_rq( 'account_order', $o );
            }
        } else {
            echo '<td class="no_orders" colspan="4">Нет ни одного заказа</td>';
        }
    }
    
    public function user_relogin()
    {
        if (isset( $this->registry[ 'userdata' ] ) && $this->registry[ 'userdata' ][ 'type' ] == 2) {
            $_SESSION[ 'user_id' ] = $_POST[ 'user_id' ];
        }
    }
    
}
