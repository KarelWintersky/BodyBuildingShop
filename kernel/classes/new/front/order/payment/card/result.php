<?php

class Front_Order_Payment_Card_Result
{
    
    private $registry;
    
    private $Front_Order_Mail_Card;
    private $Front_Order_Write_Ostatok;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Order_Mail_Card = new Front_Order_Mail_Card( $this->registry );
        $this->Front_Order_Write_Ostatok = new Front_Order_Write_Ostatok( $this->registry );
    }
    
    private function check_sum($ai, $sum_from_yandex)
    {
        if (!$ai || !is_numeric( $ai )) return false;
        
        $qLnk = mysql_query( sprintf( "
				SELECT
					overall_sum,
					from_account
				FROM
					orders
				WHERE
					ai = '%d'
				",
            $ai
        ) );
        $order = mysql_fetch_assoc( $qLnk );
        if (!$order) return false;
        
        $sum_from_order = $order[ 'overall_sum' ] - $order[ 'from_account' ];
        $sum_from_order = $sum_from_order / 0.98; //проверка тоже вместе с возложением комиссии на покупателя
        
        return ($sum_from_yandex >= $sum_from_order);
    }
    
    public function do_result($path)
    {
        if (count( $path )) Front_Order_Payment_Card_Helper::goto_index();
        
        $string2hash = array(
            $_POST[ 'notification_type' ],
            $_POST[ 'operation_id' ],
            $_POST[ 'amount' ],
            $_POST[ 'currency' ],
            $_POST[ 'datetime' ],
            $_POST[ 'sender' ],
            $_POST[ 'codepro' ],
            $this->registry[ 'config' ][ 'yandex_money' ][ 'secret' ],
            $_POST[ 'label' ],
        );
        $string2hash = implode( '&', $string2hash );
        $hash = sha1( $string2hash );
        
        if ($hash != $_POST[ 'sha1_hash' ]) return false;
        
        if (!$this->check_sum( $_POST[ 'label' ], $_POST[ 'withdraw_amount' ] )) return false;
        
        $this->update_order( $_POST[ 'label' ] );
        
        $this->Front_Order_Write_Ostatok->succesfullyRemoveReserveByAI( $_POST[ 'label' ] );
        
        $this->Front_Order_Mail_Card->send_letter( $_POST[ 'label' ] );
    }
    
    private function update_order($ai)
    {
        mysql_query( sprintf( "
				UPDATE
					orders
				SET
					status = 3,
					payed_on = NOW()
				WHERE
					ai = '%d';
				",
            $ai
        ) );
    }
    
}

