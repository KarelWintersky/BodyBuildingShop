<?php

class Statistics
{
    
    private $registry;
    
    public function __construct($registry, $frompage = true)
    {
        $this->registry = $registry;
        $this->registry->set( 'statistics', $this );
        
        if ($frompage) {
            $route = $this->registry[ 'aias_path' ];
            array_shift( $route );
            
            if (count( $route ) == 0) {
                $this->registry[ 'f_404' ] = false;
                $this->registry[ 'template' ]->set( 'c', 'statistics/checkzeros' );
            } elseif (count( $route ) == 1 && $this->sub_level_check( $route[ 0 ] )) {
                $this->registry[ 'template' ]->set( 'c', 'statistics/'.$route[ 0 ] );
                $this->registry[ 'f_404' ] = false;
            }
            
            $this->registry[ 'sub_aias_path' ] = $route;
            
            $Adm_Statistics = new Adm_Statistics( $this->registry );
            $Adm_Statistics->template_vars();
        }
        
    }
    
    private function sub_level_check($alias)
    {
        $qLnk = mysql_query( "
							SELECT
								main_parts.*
							FROM
								main_parts
							WHERE
								main_parts.parent_id <> 0
								AND
								main_parts.alias = '".$alias."'
							LIMIT 1;
							" );
        if (mysql_num_rows( $qLnk ) > 0) {
            $this->registry[ 'sub_level_info' ] = mysql_fetch_assoc( $qLnk );
            return true;
        }
        return false;
    }
    
    private function item_rq($name, $a = NULL)
    {
        require($this->registry[ 'template' ]->TF.'item/statistics/'.$name.'.html');
    }
    
    public function check_zeros()
    {
        $etal_parent = 0;
        $etal_level = 0;
        $qLnk = mysql_query( "
							SELECT
								goods.id,
								goods.barcode,
								goods.name,
								goods.price_2,
								goods.weight,
								goods.packing,
								goods.present,
								goods.published,
								levels.id AS level_id,
								levels.name AS level_name,
								parent_tbl.id AS parent_id,
								parent_tbl.name AS parent_name
							FROM
								goods
							LEFT OUTER JOIN levels ON levels.id = goods.level_id
							LEFT OUTER JOIN levels AS parent_tbl ON levels.parent_id = parent_tbl.id
							WHERE
								(goods.price_2 = 0
								OR
								goods.price_1 = 0
								OR
								goods.weight <= 0
								OR
								goods.packing = '')
								AND
								goods.id <> 601
							ORDER BY
								parent_tbl.sort ASC,
								levels.sort ASC,
								goods.sort ASC;
							" );
        while ($g = mysql_fetch_assoc( $qLnk )) {
            $g[ 'display_name' ] = ($g[ 'packing' ] != '') ? $g[ 'name' ].', '.$g[ 'packing' ] : $g[ 'name' ];
            
            $g[ 'parent_change' ] = ($etal_parent != $g[ 'parent_id' ]) ? true : false;
            $g[ 'level_change' ] = ($etal_level != $g[ 'level_id' ]) ? true : false;
            
            $g[ 'published_class' ] = ($g[ 'published' ] == 1) ? '' : 'unpub';
            
            $this->item_rq( 'check_zeros', $g );
            
            $etal_parent = $g[ 'parent_id' ];
            $etal_level = $g[ 'level_id' ];
        }
    }
    
    public function main_statistics()
    {
        $qLnk = mysql_query( "
							SELECT
								DATE(orders.made_on) as day,
								COUNT(*) AS count,
								SUM(orders.overall_price) AS overall_price,
								AVG(orders.overall_price) AS average_price,
								(SELECT COUNT(*) FROM orders AS orders_last WHERE DATE(orders_last.made_on) = DATE_SUB(DATE(orders.made_on),INTERVAL 1 YEAR)) AS last_year,
								(SELECT COUNT(*) FROM orders AS orders_prelast WHERE DATE(orders_prelast.made_on) = DATE_SUB(DATE(orders.made_on),INTERVAL 2 YEAR)) AS prelast_year
							FROM
								orders
							INNER JOIN users ON users.id = orders.user_id
							WHERE
								orders.made_on > DATE_SUB(NOW(),INTERVAL 30 DAY)
								AND
								users.type = 0
							GROUP BY
								DATE(orders.made_on)
							ORDER BY
								orders.made_on DESC
							" );
        while ($s = mysql_fetch_assoc( $qLnk )) {
            
            $s[ 'weekend_class' ] = (in_array( date( 'w', strtotime( $s[ 'day' ] ) ), array( 0, 6 ) )) ? 'weekend' : '';
            
            $this->item_rq( 'main_statistics', $s );
        }
    }
    
    public function stat_goods_ids()
    {
        $goods = array();
        $qLnk = mysql_query( "
							SELECT
								goods_stat_list.goods_id
							FROM
								goods_stat_list;
							" );
        while ($g = mysql_fetch_assoc( $qLnk )) {
            $goods[] = intval( $g[ 'goods_id' ] );
        }
        
        echo json_encode( $goods );
    }
    
    public function stat_goods_table_item($goods_id)
    {
        $qLnk = mysql_query( "
							SELECT
								goods.name,
								goods.id,
								goods.level_id AS goods_level_id,
								levels.parent_id AS goods_parent_id,
								IFNULL((
										SELECT
											SUM(orders_goods.amount)
										FROM
											orders_goods
										INNER JOIN orders ON CONCAT_WS('/',orders.id,orders.user_num,orders.payment_method) = orders_goods.order_id
										WHERE
											orders_goods.goods_id = goods.id
											AND
											(orders.payment_method = 'П' OR orders.payment_method = 'W')
											AND
											orders.status IN (2,3)
									),0) AS p1,
								IFNULL(
										(
											SELECT
												SUM(orders_goods.amount)
											FROM
												orders_goods
											INNER JOIN orders ON CONCAT_WS('/',orders.id,orders.user_num,orders.payment_method) = orders_goods.order_id
											WHERE
												orders_goods.goods_id = goods.id
												AND
												(orders.payment_method = 'П' OR orders.payment_method = 'W')
												AND
												orders.status = 1
										),0) AS p2,
								IFNULL(
										(
											SELECT
												SUM(orders_goods.amount)
											FROM
												orders_goods
											INNER JOIN orders ON CONCAT_WS('/',orders.id,orders.user_num,orders.payment_method) = orders_goods.order_id
											WHERE
												orders_goods.goods_id = goods.id
												AND
												(orders.payment_method = 'Н' OR orders.payment_method = 'H')
												AND
												orders.status <> 4
										),0) AS p3
							FROM
								goods_stat_list
							INNER JOIN goods ON goods.id = goods_stat_list.goods_id
							INNER JOIN levels ON levels.id = goods.level_id
							WHERE
								goods_stat_list.goods_id = '".$goods_id."'
							" );
        while ($g = mysql_fetch_assoc( $qLnk )) {
            echo json_encode( $g );
        }
    }
    
    public function stat_goods_table()
    {
        $qLnk = mysql_query( "
							SELECT
								goods.name,
								goods.id,
								goods.level_id AS goods_level_id,
								levels.parent_id AS goods_parent_id,
								IFNULL((
										SELECT
											SUM(orders_goods.amount)
										FROM
											orders_goods
										INNER JOIN orders ON CONCAT_WS('/',orders.id,orders.user_num,orders.payment_method) = orders_goods.order_id
										WHERE
											orders_goods.goods_id = goods.id
											AND
											(orders.payment_method = 'П' OR orders.payment_method = 'W')
											AND
											orders.status IN (2,3)
									),0) AS p1,
								IFNULL(
										(
											SELECT
												SUM(orders_goods.amount)
											FROM
												orders_goods
											INNER JOIN orders ON CONCAT_WS('/',orders.id,orders.user_num,orders.payment_method) = orders_goods.order_id
											WHERE
												orders_goods.goods_id = goods.id
												AND
												(orders.payment_method = 'П' OR orders.payment_method = 'W')
												AND
												orders.status = 1
										),0) AS p2,
								IFNULL(
										(
											SELECT
												SUM(orders_goods.amount)
											FROM
												orders_goods
											INNER JOIN orders ON CONCAT_WS('/',orders.id,orders.user_num,orders.payment_method) = orders_goods.order_id
											WHERE
												orders_goods.goods_id = goods.id
												AND
												(orders.payment_method = 'Н' OR orders.payment_method = 'H')
												AND
												orders.status <> 4
										),0) AS p3
							FROM
								goods_stat_list
							INNER JOIN goods ON goods.id = goods_stat_list.goods_id
							INNER JOIN levels ON levels.id = goods.level_id
							" );
        while ($g = mysql_fetch_assoc( $qLnk )) {
            $this->item_rq( 'stat_goods_table', $g );
        }
    }
    
    public function del_goods_stat()
    {
        if (isset( $_POST[ 'del' ] )) {
            mysql_query( "DELETE FROM goods_stat_list WHERE goods_stat_list.goods_id IN (".implode( ",", array_keys( $_POST[ 'del' ] ) ).");" );
        }
    }
    
    public function add_goods_stat()
    {
        if ($_POST[ 'block' ] != '') {
            $lines = preg_split( "/[\n\r]+/s", $_POST[ 'block' ] );
            
            foreach ($lines as $key => $l) $lines[ $key ] = trim( $l );
            
            if (count( $lines ) > 0) {
                $goods = array();
                $qLnk = mysql_query( "
									SELECT
										goods.id
									FROM
										goods
									WHERE
										goods.barcode IN (".implode( ",", $lines ).")
									" );
                while ($g = mysql_fetch_assoc( $qLnk )) {
                    $goods[] = '('.$g[ 'id' ].')';
                }
                
                if (count( $goods ) > 0) {
                    mysql_query( "
								INSERT INTO
									goods_stat_list
									(goods_id)
									VALUES
									".implode( ", ", $goods )."
								" );
                }
                
            }
        }
    }
    
}
