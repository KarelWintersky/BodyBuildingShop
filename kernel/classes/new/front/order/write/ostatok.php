<?php
Class Front_Order_Write_Ostatok{

    private $registry;

    public function __construct($registry){
        $this->registry = $registry;
    }

    /**
     * Резервируем остаток при создании заказа
     */
    public function doReserve($orderNum){
        $ostatkiToReserve = array();
        $qLnk = mysql_query(sprintf("
            SELECT
              ostatki.id,
              orders_goods.amount
            FROM
              orders_goods
            INNER JOIN ostatki ON ostatki.barcode = orders_goods.goods_barcode
            WHERE
              orders_goods.order_id = '%s'
              AND
              ostatki.value > 0
            ", $orderNum));
        while($row = mysql_fetch_assoc($qLnk)){
            $ostatkiToReserve[$row['id']] = $row['amount'];
        }

        foreach($ostatkiToReserve as $ostatok_id => $amount){
            mysql_query(sprintf("
                INSERT INTO
                  rezerv
                    (ostatok_id, order_id, amount)
                  VALUES
                    ('%d', '%s', '%d')
                ",
                $ostatok_id,
                $orderNum,
                $amount
                ));

            mysql_query(sprintf("
                UPDATE
                    ostatki
                SET
                    value = value - %d
                WHERE
                    id = '%d'
                ",
                $amount,
                $ostatok_id
                ));
        }
    }

    public function succesfullyRemoveReserve($orderNum){
        mysql_query(sprintf("
            DELETE FROM
              rezerv
            WHERE
              order_id = '%s'
            ", $orderNum));
    }

    public function unhappyRemoveReserve($orderNum){
        $qLnk = mysql_query(sprintf("
            SELECT
              *
            FROM
              rezerv
            WHERE
              order_id = '%s'
            ", $orderNum));
        $row = mysql_fetch_assoc($qLnk);

        if($row){
            mysql_query(sprintf("
                UPDATE
                    ostatki
                SET
                    value = value + %d
                WHERE
                    id = '%d'
                ",
                $row['amount'],
                $row['ostatok_id']
                ));

            mysql_query(sprintf("
                DELETE FROM
                  rezerv
                WHERE
                  order_id = '%s'
                ", $orderNum));
        }
    }

    public function succesfullyRemoveReserveByAI($orderAI){
        $qLnk = mysql_query(sprintf("
            SELECT
                *
            FROM
                orders
            WHERE
                ai = '%d'
            ", $orderAI));
        $row = mysql_fetch_assoc($qLnk);

        if($row){
            $orderNum = sprintf('%s/%s/%s',
                $row['id'],
                $row['user_num'],
                $row['payment_method']
                );

            $this->succesfullyRemoveReserve($orderNum);
        }
    }
}
?>