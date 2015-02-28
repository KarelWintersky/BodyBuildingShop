<?php
Class Adm_Orders_Goods Extends Common_Rq{

	private $registry;
	
	private $Adm_Orders_Goods_Barcodes;
	private $Adm_Orders_Goods_Ids;
	
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Adm_Orders_Goods_Barcodes = new Adm_Orders_Goods_Barcodes($this->registry);
		$this->Adm_Orders_Goods_Ids = new Adm_Orders_Goods_Ids($this->registry);
	}
		
	private function is_barcodes($goods){
		/*
		 * проверяем, по какой системе сделан заказ - старой (без штрихкодов/признаков) или новой
		 * */
		
		return ($goods[0]['goods_id']==0);
	}

	private function get_from_orders($num){
		$goods = array();
		$qLnk = mysql_query(sprintf("
				SELECT
					*
				FROM
					orders_goods
				WHERE
					order_id = '%s'
				ORDER BY
					final_price DESC;
				",
				$num
				));
		while($g = mysql_fetch_assoc($qLnk)) $goods[] = $g;
			
		return $goods;		
	}
	
	private function get_gift($order,$goods){
		if(is_null($order['gift_barcode'])) return $goods;

		$Front_Order_Data_Cart_Gift = new Front_Order_Data_Cart_Gift($this->registry);
		
		$data = $Front_Order_Data_Cart_Gift->get_gift($order['gift_barcode']); 
		
		$gift = (isset($data['barcode']))
			 ? array(
					'goods_full_name' => $data['name'], 
					'goods_id' => $data['goods_id'], 
					'level_id' => $data['level_id'], 
					'parent_id' => $data['parent_id'], 
					'goods_barcode' => $data['barcode'], 
					'goods_packing' => $data['packing'],
					'feature' => $data['feature'],
					'goods_feats_str' => '', 
					'amount' => 1, 
					'price' => 0, 
					'discount' => 0, 
					'final_price' => 0, 
					)
			: array(
					'goods_full_name' => $data['name'],
					'goods_id' => false,
					'level_id' => false,
					'parent_id' => false,
					'goods_barcode' => false,
					'goods_packing' => false,
					'feature' => false,
					'goods_feats_str' => '',
					'amount' => 1,
					'price' => 0,
					'discount' => 0,
					'final_price' => 0,					
					);
		
		$goods[] = $gift;
		 
		return $goods;
	}
	
	public function get_goods($num){
		$goods = $this->get_from_orders($num);
		if(!count($goods)) return false;
		
		$goods = ($this->is_barcodes($goods))
			? $this->Adm_Orders_Goods_Barcodes->get_data($goods)
			: $this->Adm_Orders_Goods_Ids->get_data($goods);
		
		$goods = $this->get_gift($order,$goods);

		return $goods;
	}
	
	public function goods_list($order){		
		$goods = $this->get_goods($order['num']);
		
		$html = array();
		foreach($goods as $g){
			$g['name'] = (!$g['goods_full_name'] && isset($g['goods_name']))
				? $g['goods_name']
				: $g['goods_full_name'];
				
			$g['final_price'] = $g['final_price']*$g['amount'];
				
			$html[] = $this->do_rq('item',$g,true);
		}
		
		return implode('',$html);
	}
	
}
?>