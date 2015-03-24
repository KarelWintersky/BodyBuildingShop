<?php
Class Adm_Catalog_Statistics_Data{

	private $registry;
			
	public function __construct($registry){
		$this->registry = $registry;
	}

	private function old_orders(){
		/*
		 * старая система заказов
		 * */
		
		$orders = array();
		
		$qLnk = mysql_query(sprintf("
				SELECT DISTINCT
					order_id,
					goods_feats_str,
					amount
				FROM
					orders_goods
				WHERE
					goods_id = '%d'
				",
				$this->registry['good']['id']
				));
		while($o = mysql_fetch_assoc($qLnk))
			$orders[$o['order_id']][] = array(
					'amount' => $o['amount'],
					'goods_barcode' => '',
					'feats' => $o['goods_feats_str'],
			);
		
		return $orders;
	}
	
	private function new_orders(&$barcodes){
		/*
		 * новая система заказов
		 * */
		
		$orders = array();
		
		$barcodes = array();
		$qLnk = mysql_query(sprintf("
				SELECT
					barcode,
					packing,
					feature
				FROM
					goods_barcodes
				WHERE
					goods_id = '%d'
				",$this->registry['good']['id']));
		while($b = mysql_fetch_assoc($qLnk)) $barcodes[$b['barcode']] = $b;
		
		if(!count($barcodes)) return $orders;
		
		$keys = array();
		foreach($barcodes as $k => $arr) $keys[] = sprintf("'%s'",$k);
	
		$qLnk = mysql_query(sprintf("
				SELECT
					order_id,
					goods_barcode,
					goods_feats_str,
					amount
				FROM
					orders_goods
				WHERE
					goods_barcode IN (%s)
				",
				implode(",",$keys)
		));
		while($o = mysql_fetch_assoc($qLnk))
			$orders[$o['order_id']][] = array(
					'amount' => $o['amount'],
					'goods_barcode' => $o['goods_barcode'],
					'feats' => $o['goods_feats_str'],
			);
		
		return $orders;
	}
	
	public function get_data(){
		$orders = $this->old_orders() + $this->new_orders($barcodes); 
		
		$ids = array();
		foreach($orders as $key => $arr) $ids[] = sprintf("'%s'",$key);
	
		$data = array();
		$qLnk = mysql_query(sprintf("
				SELECT SQL_CALC_FOUND_ROWS
					CONCAT_WS('/',id,user_num,payment_method) AS text_id,
					id,
					user_num,
					payment_method,
					made_on,
					status
				FROM
					orders
				WHERE
					CONCAT_WS('/',id,user_num,payment_method) IN (%s)
				ORDER BY
					made_on DESC
				",
				implode(",",$ids)
				));
		while($o = mysql_fetch_assoc($qLnk)){ 
			
			$pf = array(); $amount = 0;
			if(isset($orders[$o['text_id']])){
				foreach($orders[$o['text_id']] as $goods){
					$amount+=$goods['amount'];
			
					$packing = (isset($barcodes[$goods['goods_barcode']]['packing']))
						? $barcodes[$goods['goods_barcode']]['packing']
						: false;
					
					$feature = (isset($barcodes[$goods['goods_barcode']]['feature']))
						? $barcodes[$goods['goods_barcode']]['feature']
						: false;
			
					$str = array($packing,$feature);
					foreach($str as $key => $val) if(!$val) unset($str[$key]);
			
					if(count($str)) $pf[] = implode(', ',$str);
				}
			}
			
			$o['amount'] = $amount;
			$o['pf'] = implode('<br>',$pf);
			
			//$o['status_txt'] = (isset($Orders->statuses[$o['status']])) ? $Orders->statuses[$o['status']] : '';
			
			
			$data[] = $o;
		}
		
		return $data;
	}
	
}
?>